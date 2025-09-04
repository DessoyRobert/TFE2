<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Models\Build;
use App\Models\Component;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // --- Auth obligatoire
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Authentification requise.',
                'errors'  => ['auth' => ['Veuillez vous connecter pour passer commande.']],
            ], 401);
        }
        $userId = (int) $user->id;

        // --- Validation d’entrée
        $rules = [
            'build_id'        => ['required','integer','exists:builds,id'],
            'component_ids'   => ['nullable','array'],
            'component_ids.*' => ['integer','exists:components,id'],

            'customer_first_name'    => ['nullable','string','max:100'],
            'customer_last_name'     => ['nullable','string','max:100'],
            'customer_email'         => ['nullable','email','max:150'],
            'customer_phone'         => ['nullable','string','max:30'],

            'shipping_address_line1' => ['required','string','max:255'],
            'shipping_address_line2' => ['nullable','string','max:255'],
            'shipping_city'          => ['required','string','max:120'],
            'shipping_postal_code'   => ['required','string','max:20'],
            'shipping_country'       => ['required','string','size:2'],

            'payment_method'         => ['nullable','in:bank_transfer,card,cash,cod'],
            'currency'               => ['nullable','in:EUR,USD'],
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['message' => 'Données invalides.', 'errors' => $v->errors()], 422);
        }
        $data = $v->validated();

        $buildId = (int) $data['build_id'];

        // --- Récup build + components
        $build = Build::with(['components' => function ($q) {
            $q->select('components.id', 'components.name', 'components.price')
              ->withPivot(['quantity', 'price_at_addition']);
        }])->findOrFail($buildId);

        // --- Contrôle propriétaire (simple si pas de Policy)
        if ((int) $build->user_id !== $userId) {
            return response()->json([
                'message' => 'Accès refusé.',
                'errors'  => ['build_id' => ['Vous ne pouvez pas commander ce build.']],
            ], 403);
        }

        // --- Normalisation client
        $customerFirst = trim((string) ($data['customer_first_name'] ?? ''));
        $customerLast  = trim((string) ($data['customer_last_name'] ?? ''));
        $customerEmail = trim((string) ($data['customer_email'] ?? ''));

        if ($customerEmail === '' && !empty($user->email)) {
            $customerEmail = (string) $user->email;
        }
        if ($customerFirst === '' || $customerLast === '') {
            $name = (string) ($user->name ?? '');
            if ($name !== '') {
                $parts = preg_split('/\s+/', trim($name)) ?: [];
                $customerFirst = $customerFirst ?: ($parts[0] ?? 'Client');
                $customerLast  = $customerLast  ?: (implode(' ', array_slice($parts, 1)) ?: 'PCBuilder');
            } else {
                $customerFirst = $customerFirst ?: 'Client';
                $customerLast  = $customerLast  ?: 'PCBuilder';
            }
        }

        // --- Sélection des composants : ceux passés ou tous ceux du build
        $incomingIds = collect($data['component_ids'] ?? [])
            ->map(fn($v) => is_numeric($v) ? (int) $v : null)
            ->filter(fn($v) => $v !== null)
            ->values();

        $selectedIds = $incomingIds->isEmpty()
            ? $build->components->pluck('id')
            : $incomingIds->intersect($build->components->pluck('id'))->values();

        if ($selectedIds->isEmpty()) {
            return response()->json([
                'message' => 'Le build ne contient aucun composant.',
                'errors'  => ['component_ids' => ['Aucun composant valide trouvé pour ce build.']],
            ], 422);
        }

        // --- Idempotence + verrou
        $idempotencyKey = (string) ($request->header('Idempotency-Key') ?: Str::uuid());
        $lockKey = "checkout:lock:user:{$userId}:build:{$buildId}";

        return Cache::lock($lockKey, 10)->block(5, function () use ($build, $selectedIds, $data, $userId, $customerFirst, $customerLast, $customerEmail, $idempotencyKey) {

            return DB::transaction(function () use ($build, $selectedIds, $data, $userId, $customerFirst, $customerLast, $customerEmail, $idempotencyKey) {

                $items = $build->components->whereIn('id', $selectedIds);

                // --- Totaux
                $subtotal = 0.00;
                foreach ($items as $component) {
                    $qty  = (int) ($component->pivot->quantity ?? 1);
                    $unit = (float) ($component->pivot->price_at_addition ?? $component->price ?? 0);
                    $subtotal += $unit * $qty;
                }

                $shippingCountry = strtoupper((string) ($data['shipping_country'] ?? 'BE'));
                $vatRate    = $shippingCountry === 'BE' ? 0.21 : 0.00; // simplifié
                $tax        = round($subtotal * $vatRate, 2);
                $freeThresh = (float) env('CHECKOUT_FREE_SHIPPING_THRESHOLD', 1500.00);
                $flatShip   = (float) env('CHECKOUT_FLAT_SHIPPING', 14.90);
                $shipping   = $subtotal >= $freeThresh ? 0.00 : $flatShip;
                $discount   = 0.00;
                $grand      = round($subtotal + $tax + $shipping - $discount, 2);

                // --- Création commande
                $order = Order::create([
                    'user_id' => $userId,

                    // Client
                    'customer_first_name'    => $customerFirst,
                    'customer_last_name'     => $customerLast,
                    'customer_email'         => $customerEmail,
                    'customer_phone'         => $data['customer_phone'] ?? null,

                    // Adresse
                    'shipping_address_line1' => $data['shipping_address_line1'],
                    'shipping_address_line2' => $data['shipping_address_line2'] ?? null,
                    'shipping_city'          => $data['shipping_city'],
                    'shipping_postal_code'   => $data['shipping_postal_code'],
                    'shipping_country'       => $shippingCountry,

                    // Totaux
                    'subtotal'               => round($subtotal, 2),
                    'shipping_cost'          => round($shipping, 2),
                    'discount_total'         => round($discount, 2),
                    'tax_total'              => round($tax, 2),
                    'grand_total'            => round($grand, 2),

                    // Statuts / paiement
                    'status'                 => 'pending',
                    'payment_status'         => 'unpaid',
                    'payment_method'         => $data['payment_method'] ?? 'bank_transfer',
                    'currency'               => strtoupper((string) ($data['currency'] ?? 'EUR')),

                    // Meta
                    'meta'                   => ['idempotency_key' => $idempotencyKey],
                ]);

                // --- Ligne "Build" (regroupement : 0€ pour éviter double comptage si l'UI somme les lignes)
                OrderItem::updateOrCreate(
                    [
                        'order_id'         => $order->id,
                        'purchasable_type' => Build::class,
                        'purchasable_id'   => $build->id,
                    ],
                    [
                        'quantity'   => 1,
                        'unit_price' => 0,
                        'line_total' => 0,
                        'snapshot'   => [
                            'build_id'      => $build->id,
                            'name'          => $build->name ?? ("Build #{$build->id}"),
                            'component_ids' => $selectedIds->values()->all(),
                        ],
                    ]
                );

                // --- Lignes "Composants" (détail + prix)
                foreach ($items as $component) {
                    $qty  = (int) ($component->pivot->quantity ?? 1);
                    $unit = (float) ($component->pivot->price_at_addition ?? $component->price ?? 0);

                    OrderItem::create([
                        'order_id'         => $order->id,
                        'purchasable_type' => Component::class,
                        'purchasable_id'   => $component->id,
                        'quantity'         => $qty,
                        'unit_price'       => round($unit, 2),
                        'line_total'       => round($unit * $qty, 2),
                        'snapshot'         => [
                            'component_id' => $component->id,
                            'name'         => $component->name,
                            'price'        => (float) $component->price,
                        ],
                    ]);
                }

                // --- Coordonnées virement (si bank_transfer)
                $bank = null;
                $pm   = $order->payment_method;
                if ($pm === 'bank_transfer') {
                    $bank = [
                        'holder'           => env('BANK_HOLDER', 'PCBuilder SRL'),
                        'iban'             => env('BANK_IBAN', 'BE00 0000 0000 0000'),
                        'bic'              => env('BANK_BIC', 'XXXXXX'),
                        'reference'        => 'ORDER-' . $order->id,
                        'payment_deadline' => now()->addDays((int) env('BANK_PAYMENT_DAYS', 7))->format('Y-m-d'),
                    ];
                }

                return response()->json([
                    'message'        => 'Commande créée.',
                    'order_id'       => $order->id,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'currency'       => $order->currency,
                    'amounts' => [
                        'subtotal' => $order->subtotal,
                        'shipping' => $order->shipping_cost,
                        'tax'      => $order->tax_total,
                        'discount' => $order->discount_total,
                        'grand'    => $order->grand_total,
                    ],
                    'bank'          => $bank,
                    'redirect_url'  => route('checkout.show', ['order' => $order->id]),
                ], 201);
            });
        });
    }
}

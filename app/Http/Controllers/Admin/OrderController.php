<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;

// Facultatif (endpoints JSON avancés) : décommente si tu as ajouté ces classes
use App\Services\OrderService;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderAddItemRequest;
use App\Http\Requests\OrderMarkPaidRequest;
use App\Http\Requests\OrderStatusRequest;

/**
 * Class OrderController (Admin)
 *
 * Contrôleur d’administration pour la consultation et la gestion des commandes.
 * - Rend les écrans Inertia (liste).
 * - Expose, en option, une API JSON interne à l’admin (création commande, ajout/suppression d’items, marquage payé).
 *
 * Bonnes pratiques appliquées :
 *  - Validation systématique des entrées (Form Requests côté JSON).
 *  - Aucune confiance dans les totaux transmis par le client : recalcul côté serveur via un service métier.
 *  - Pagination pour limiter la charge et accélérer l’affichage des listes.
 *  - Mapping/DTO léger côté index pour ne renvoyer que les colonnes utiles à la vue.
 *
 * @package App\Http\Controllers\Admin
 */
class OrderController extends Controller
{
    
    /**
     * Service métier pour la gestion des commandes (création, recalcul des totaux, paiements, etc.).
     * Facultatif : si non utilisé, commentez la propriété + le constructeur.
     */
    private OrderService $service;

    /**
     * Injection de dépendances.
     * @param  OrderService  $service  Service d’orchestration métier autour d’Order/OrderItem/Payment.
     */
    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    /**
     * Liste paginée des commandes (écran Admin via Inertia).
     *
     * Filtrage simple par statut. Les données sont “mappées” (through) afin de n’envoyer
     * à la page que les champs strictement nécessaires à l’affichage.
     *
     * @param  Request  $request  Reçoit éventuellement ?status=pending|paid|...
     * @return InertiaResponse    Rend la page 'Admin/Orders/Index' avec 'orders' et 'filters'
     */
    public function index(Request $request): InertiaResponse
    {
        $orders = Order::query()
            ->when(
                $request->input('status'),
                fn ($q, $s) => $q->where('status', $s)
            )
            ->orderByDesc('id')
            ->paginate(20)
            ->through(function (Order $o) {
                return [
                    'id'             => $o->id,
                    'created_at'     => $o->created_at->toDateTimeString(),
                    // Accessor Eloquent (customerName) exposé en snake_case -> customer_name
                    'customer'       => $o->customer_name,
                    'email'          => $o->customer_email,
                    'status'         => $o->status,
                    'payment_status' => $o->payment_status,
                    'grand_total'    => (string) $o->grand_total, // cast string pour affichage stable
                    'currency'       => $o->currency,
                ];
            });

        return Inertia::render('Admin/Orders/Index', [
            'orders'  => $orders,
            'filters' => [
                'status' => $request->input('status'),
            ],
        ]);
    }

    /**
     * Mise à jour du statut d’une commande (workflow logistique côté admin).
     *
     * ⚠ Différencie bien :
     *   - status          : cycle de vie logistique (pending, preparing, shipped, delivered, canceled, refunded…)
     *   - payment_status  : état du paiement (unpaid, paid, failed, refunded…)
     *
     * Cette méthode met uniquement à jour le **status** (logistique).
     * Si tu veux une version JSON uniforme, crée aussi un OrderStatusRequest et renvoie JSON au lieu d’un redirect.
     *
     * @param  Request  $request  Contient 'status' parmi la whitelist
     * @param  Order    $order    Modèle lié par route-model binding
     * @return RedirectResponse   Retour à la page précédente avec message flash
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:pending,paid,preparing,shipped,delivered,canceled,refunded'
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('success', 'Statut mis à jour.');
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // ↓ Endpoints JSON facultatifs pour l’admin (pilotage avancé)
    //    Décommente/active ces actions si tu exposes une API interne à l’admin.
    //    Pré-requis : OrderService + Form Requests fournis précédemment.
    // ─────────────────────────────────────────────────────────────────────────────

    /**
     * [JSON - ADMIN] Liste paginée des commandes (incluant relations).
     * Utile si tu veux hydrater des tableaux via fetch/AJAX plutôt que via Inertia props.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list()
    {
        return Order::with(['items', 'payments'])
            ->latest()
            ->paginate(20);
    }

    /**
     * [JSON - ADMIN] Détail d’une commande, incluant items (et leur cible morphique) + paiements.
     *
     * @param  Order  $order
     * @return Order
     */
    public function show(Order $order): Order
    {
        return $order->load(['items.purchasable', 'payments']);
    }

    /**
     * [JSON - ADMIN] Création d’une commande (cliente, adresses, items éventuels).
     * Les totaux sont recalculés côté serveur via OrderService (zéro confiance dans les montants entrants).
     *
     * @param  OrderStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(OrderStoreRequest $request)
    {
        $order = $this->service->create($request->validated());
        return response()->json($order, 201);
    }

    /**
     * [JSON - ADMIN] Ajout d’un item dans une commande existante.
     * (purchasable = Build ou Component via morph).
     *
     * @param  OrderAddItemRequest  $request
     * @param  Order                $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(OrderAddItemRequest $request, Order $order)
    {
        $order = $this->service->addItem($order, $request->validated());
        return response()->json($order);
    }

    /**
     * [JSON - ADMIN] Suppression d’un item d’une commande.
     * Recalcule automatiquement les totaux pour garantir la cohérence comptable.
     *
     * @param  Order  $order
     * @param  int    $orderItemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(Order $order, int $orderItemId)
    {
        $order = $this->service->removeItem($order, $orderItemId);
        return response()->json($order);
    }

    /**
     * [JSON - ADMIN] Marque une commande comme payée et enregistre un paiement.
     * Met à jour payment_status et (si pertinent) le status logistique (ex: pending → processing).
     *
     * @param  OrderMarkPaidRequest  $request
     * @param  Order                 $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function markPaid(OrderMarkPaidRequest $request, Order $order)
    {
        $order = $this->service->markPaid($order, $request->validated());
        return response()->json($order);
    }

    /**
     * [JSON - ADMIN] Mise à jour de statut (version JSON homogène, si souhaitée).
     * Permet d’unifier la validation via OrderStatusRequest tout en restant en API.
     *
     * @param  OrderStatusRequest  $request
     * @param  Order               $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusJson(OrderStatusRequest $request, Order $order)
    {
        $order = $this->service->updateStatus($order, $request->validated()['status']);
        return response()->json($order);
    }
}

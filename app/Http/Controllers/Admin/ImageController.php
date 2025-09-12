<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cloudinary\Cloudinary;
use App\Models\Image;
use App\Models\Component;
use App\Models\Build;

class ImageController extends Controller
{
    
    protected function transformCdn(?string $url, ?int $w = null): ?string
    {
        if (!$url) return $url;
        if (str_contains($url, '/upload/')) {
            $insert = 'f_auto,q_auto' . ($w ? ',w_' . (int)$w : '');
            return preg_replace('#/upload/#', '/upload/' . $insert . '/', $url, 1);
        }
        return $url;
    }

    public function index(Request $request): Response
    {
        $q = trim((string) $request->get('q', ''));

        $images = Image::query()
            ->with('imageable')
            ->when($q !== '', fn($qq) =>
                $qq->where(function($w) use ($q) {
                    $w->where('url', 'LIKE', "%{$q}%")
                      ->orWhere('imageable_type', 'LIKE', "%{$q}%")
                      ->orWhere('imageable_id', $q);
                })
            )
            ->latest()
            ->paginate(24)
            ->withQueryString()
            ->through(fn($img) => [
                'id'          => $img->id,
                'url'         => $this->transformCdn($img->url, 480) ?? $img->url,
                'type'        => class_basename($img->imageable_type ?? ''),
                'ref'         => $img->imageable_id,
                'has_owner'   => (bool) $img->imageable,
                'owner_label' => $img->imageable
                    ? (($img->imageable instanceof Build)
                        ? "Build: {$img->imageable->name}"
                        : "Component: {$img->imageable->name}")
                    : '—',
            ]);

        return Inertia::render('Admin/Images/Index', [
            'filters' => ['q' => $q],
            'images'  => $images,
        ]);
    }

    public function uploadPage(): Response
    {
        return Inertia::render('Admin/Images/Upload');
    }

    public function store(Request $request, Cloudinary $cloudinary)
{
    $request->validate([
        'target_type' => 'required|string|in:component,build',
        'target_id'   => 'required|integer',
        // l’un OU l’autre
        'image'       => 'nullable|image|max:4096',
        'image_url'   => 'nullable|url|max:2048',
    ]);

    if (!$request->hasFile('image') && !$request->filled('image_url')) {
        return back()->withErrors([
            'image' => 'Veuillez choisir un fichier OU saisir une URL.',
            'image_url' => 'Veuillez choisir un fichier OU saisir une URL.',
        ]);
    }

    // Résolution du modèle cible
    $owner = match ($request->string('target_type')->toString()) {
        'component' => Component::findOrFail($request->integer('target_id')),
        'build'     => Build::findOrFail($request->integer('target_id')),
    };

    // Upload vers Cloudinary : fichier local ou URL distante
    if ($request->hasFile('image')) {
        $source = $request->file('image')->getRealPath();
    } else {
        $source = $request->string('image_url')->toString(); // ex: https://exemple.com/photo.jpg
    }

    // options Cloudinary optionnelles (ex: dossier)
    $options = [
        // 'folder' => 'jarvistech', // décommente si tu veux ranger dans un dossier
        'resource_type' => 'image',
    ];

    $response = $cloudinary->uploadApi()->upload($source, $options);

    $owner->images()->create([
        'url'       => $response['secure_url'],
        'public_id' => $response['public_id'] ?? null,
    ]);

    return redirect()->back()->with([
        'success' => 'Image uploadée avec succès.',
        'url'     => $response['secure_url'],
    ]);
}


    public function destroy(Image $image, Cloudinary $cloudinary)
    {
        if ($image->public_id) {
            try {
                $cloudinary->uploadApi()->destroy($image->public_id);
            } catch (\Throwable $e) {
                // Optionnel: log
            }
        }

        $image->delete();
        return back()->with('success', 'Image supprimée.');
    }

    public function makePrimary(Image $image)
    {
        $owner = $image->imageable;
        if (!$owner) {
            return back()->with('error', 'Aucun modèle associé.');
        }

        if ($owner instanceof Build || $owner instanceof Component) {
            $owner->img_url = $image->url;
            $owner->save();
        }

        return back()->with('success', 'Image définie comme principale.');
    }
}

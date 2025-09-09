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
    /**
     * Liste paginée des images avec recherche
     */
    public function index(Request $request): Response
    {
        $q = trim((string) $request->get('q', ''));

        $images = Image::query()
            ->with('imageable')
            ->when($q !== '', fn($qq) =>
                $qq->where('url', 'ILIKE', "%{$q}%")
                   ->orWhere('imageable_type', 'ILIKE', "%{$q}%")
                   ->orWhere('imageable_id', $q)
            )
            ->latest()
            ->paginate(24)
            ->withQueryString()
            ->through(fn($img) => [
                'id'          => $img->id,
                'url'         => $img->url,
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

    /**
     * Affiche la page d’upload
     */
    public function uploadPage(): Response
    {
        return Inertia::render('Admin/Images/Upload');
    }

    /**
     * Stocke une image sur Cloudinary et l’associe à un modèle
     */
    public function store(Request $request, Cloudinary $cloudinary)
    {
        $request->validate([
            'image'       => 'required|image|max:4096',
            'target_type' => 'required|string|in:component,build',
            'target_id'   => 'required|integer',
        ]);

        // Upload vers Cloudinary
        $response = $cloudinary->uploadApi()->upload(
            $request->file('image')->getRealPath()
        );

        $uploadedUrl = $response['secure_url'];

        // Résolution du modèle cible
        $targetType = $request->input('target_type');
        $targetId   = $request->input('target_id');

        $owner = match ($targetType) {
            'component' => Component::findOrFail($targetId),
            'build'     => Build::findOrFail($targetId),
        };

        // Associe l’image
        $owner->images()->create([
            'url' => $uploadedUrl,
        ]);

        return redirect()->back()->with([
            'success' => 'Image uploadée avec succès.',
            'url'     => $uploadedUrl,
        ]);
    }

    /**
     * Supprime une image
     */
    public function destroy(Image $image)
    {
        $image->delete();
        return back()->with('success', 'Image supprimée.');
    }

    /**
     * Définit une image comme principale (copie l’URL dans img_url du owner)
     */
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

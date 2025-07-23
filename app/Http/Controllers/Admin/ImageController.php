<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Cloudinary\Cloudinary;
use App\Models\Image;
use App\Models\Component;
use App\Models\Build;

class ImageController extends Controller
{
    /**
     * Affiche la page d’upload
     */
    public function uploadPage()
    {
        return Inertia::render('Admin/Images/Upload');
    }

    /**
     * Stocke une image sur Cloudinary et l’associe à un modèle (Build ou Component)
     */
    public function store(Request $request, Cloudinary $cloudinary)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
            'target_type' => 'required|string|in:component,build',
            'target_id' => 'required|integer',
        ]);

        // Upload vers Cloudinary
        $response = $cloudinary->uploadApi()->upload(
            $request->file('image')->getRealPath()
        );

        $uploadedUrl = $response['secure_url'];

        // Résolution du modèle cible
        $targetType = $request->input('target_type');
        $targetId = $request->input('target_id');

        $owner = match ($targetType) {
            'component' => Component::findOrFail($targetId),
            'build'     => Build::findOrFail($targetId),
        };

        // Création du lien image <-> modèle
        $owner->images()->create([
            'url' => $uploadedUrl,
        ]);

        return redirect()->back()->with([
            'success' => 'Image uploadée avec succès.',
            'url' => $uploadedUrl,
        ]);
    }
}

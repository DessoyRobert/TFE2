<?php

namespace App\Policies;

use App\Models\Build;
use App\Models\User;

class BuildPolicy
{
    /**
     * Détermine si un utilisateur (ou invité) peut voir un build.
     *
     * - Si le build est public -> tout le monde peut voir (même sans login).
     * - Sinon -> seulement le propriétaire ou un admin.
     */
    public function view(?User $user, Build $build): bool
    {
        if ($build->is_public) {
            return true;
        }

        if ($user) {
            return $user->id === $build->user_id || ($user->is_admin ?? false);
        }

        return false;
    }

    /**
     * Détermine si l’utilisateur peut créer un build.
     *
     * Tout utilisateur connecté peut créer un build.
     */
    public function create(User $user): bool
    {
        return $user !== null;
    }

    /**
     * Détermine si l’utilisateur peut mettre à jour un build.
     *
     * - Le propriétaire peut modifier son build (mais pas le rendre public).
     * - L’admin peut tout modifier (y compris is_public).
     */
    public function update(User $user, Build $build): bool
    {
        return $user->id === $build->user_id || ($user->is_admin ?? false);
    }

    /**
     * Détermine si l’utilisateur peut supprimer un build.
     *
     * - Propriétaire ou admin.
     */
    public function delete(User $user, Build $build): bool
    {
        return $user->id === $build->user_id || ($user->is_admin ?? false);
    }

    /**
     * Détermine si l’utilisateur peut voir la liste de tous les builds.
     * (utile si tu as un index public).
     *
     * Ici -> tout le monde peut voir la liste des builds publics.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }
}

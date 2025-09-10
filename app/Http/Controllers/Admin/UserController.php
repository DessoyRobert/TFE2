<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    // GET /admin/users
    public function index()
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::orderBy('id', 'desc')->paginate(15)
        ]);
    }

    // Optionnel : édition/suppression
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user
        ]);
    }

    // DELETE /admin/users/{user}
    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé !');
    }
}

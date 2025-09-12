<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Build;
use App\Models\Component;
use App\Models\Brand;
use App\Models\Category;
use Inertia\Inertia;

class DashboardController extends Controller
{
    
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users'      => User::count(),
                'builds'     => Build::count(),
                'components' => Component::count(),
                'brands'     => Brand::count(),
                'categories' => Category::count(),
            ],
        ]);
    }
}

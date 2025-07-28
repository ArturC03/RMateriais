<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardMaterialsController extends Controller
{
    public function index(Request $request)
    {
        $materials = Material::with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($material) {
                return [
                    'id' => $material->id,
                    'name' => $material->name,
                    'description' => $material->description,
                    'quantity' => $material->quantity,
                    'max_days_per_request' => $material->max_days_per_request,
                    'category' => $material->category ? [
                        'id' => $material->category->id,
                        'name' => $material->category->name,
                    ] : null,
                ];
            });

        return Inertia::render('Dashboard/Materials', [
            'materials' => $materials,
        ]);
    }
}

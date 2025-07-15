<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class RequisitionController extends Controller
{
    public function index(): Response
    {
        $materials = Material::with(['category', 'requestItems'])->get()->map(function ($material) {
            return [
                'id' => $material->id,
                'name' => $material->name,
                'description' => $material->description,
                'quantity' => $material->quantity,
                'max_days_per_request' => $material->max_days_per_request,
                'category' => $material->category,
                'requestItems' => $material->requestItems,
                'availableQuantity' => $material->availableQuantity(),
                'isAvailable' => $material->isAvailable(),
                'currentlyBorrowedQuantity' => $material->currentlyBorrowedQuantity(),
            ];
        });

        return Inertia::render('requisitions/Index', [
            'materials' => $materials,
            'categories' => Category::all(),
        ]);
    }

}

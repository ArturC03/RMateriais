<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
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
                'available_quantity' => $material->available_quantity,
                'is_available' => $material->is_available,
                'currently_borrowed_quantity' => $material->currently_borrowed_quantity,
            ];
        });

        $cart = null;
        $user = auth()->user();
        if ($user) {
            $cart = $user->cart()->load('requestItems.material.category');
        }

        return Inertia::render('requisitions/Index', [
            'materials' => $materials,
            'categories' => Category::all(),
            'cart' => $cart,
        ]);
    }

    public function cart(): Response {
        $user = auth()->user();

        if (!$user)
            throw new \Exception("User must be logged in");


        $cart = $user->cart()->load('requestItems.material.category');

        if (!$cart)
            throw new \Exception("Error getting cart");

        return Inertia::render('requisitions/Cart', [
            'cart' => $cart,
        ]);
    }

    public function placeOrder(Request $request): RedirectResponse {
        try {
            $user = auth()->user();

            if(!$user)
                return back()->withErrors(['error' => 'Não autenticado']);

            if($user->cart()->isEmpty())
                return back()->withErrors(['error' => 'O carrinho está vazio']);

            $user->cart()->makeOrder();

            return back()->with(['success' => 'Pedido realizado com sucesso']);

        } catch (\Exception $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }
    }
}

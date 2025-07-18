<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;
use Illuminate\Support\Facades\Validator;

class MaterialsController extends Controller
{
    public function addToCart(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return back()->withErrors(['error' => 'Não autenticado']);
            }

            $material = Material::findOrFail($request->input('material_id'));

            $validator = Validator::make($request->all(), [
                'material_id' => 'required|exists:materials,id',
                'quantity' => 'required|integer|min:1|max:' . $material->available_quantity,
                'days' => ['required', 'integer', 'min:1', 'max:' . $material->max_days_per_request],
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator->errors());
            }

                // Get or create cart (draft request)
            $cart = $user->cart();

            // Check if item already exists in cart
            $existingItem = $cart->requestItems()->where('material_id', $request->material_id)->first();

            if ($existingItem) {
                if($existingItem->quantity + $request->quantity > $material->available_quantity)
                    return back()->withErrors(['error' => 'Acabou o stock de "'. $existingItem->material->name.'"']);

                $existingItem->quantity += $request->quantity;
                $existingItem->save();
                return back()->with('success', 'Material adicionado ao carrinho!');
            }

            // Calculate due date
            $dueDate = now()->addDays($request->days);

            $requestItem = $cart->requestItems()->create([
                'material_id' => $request->material_id,
                'quantity' => $request->quantity,
                'requested_days' => $request->days,
                'due_date' => $dueDate,
                'returned' => false,
            ]);

            return back()->with('success', 'Material adicionado ao carrinho!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    }

    public function removeFromCart(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return back()->withErrors(['error' => 'Não autenticado']);
            }

            $validator = Validator::make($request->all(), [
                'material_id' => 'required|exists:materials,id',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator->errors());
            }

            $cart = $user->cart();

            $existingItem = $cart->requestItems()
                ->where('material_id', $request->material_id)
                ->first();

            if (!$existingItem) {
                return back()->withErrors(['error' => 'Este material não está no carrinho']);
            }

            $existingItem->delete(); // <- isto é que remove o item

            return back()->with('success', 'Material removido do carrinho com sucesso!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    }
}

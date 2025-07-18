<?php

use App\Models\Material;
use App\Models\RequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Http\Controllers\RequisitionController;
use \App\Http\Controllers\MaterialsController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::post('materiais/adicionar-ao-carrinho', [MaterialsController::class, 'addToCart'])
    ->middleware(['auth', 'verified'])->name('materials.cart.remove');

Route::post('materiais/remover-do-carrinho', [MaterialsController::class, 'removeFromCart'])
    ->middleware(['auth', 'verified'])->name('materials.cart.remove');

Route::get('/catalogo', [RequisitionController::class, 'index'])->name('requisitions.index');
Route::get('/carrinho', [RequisitionController::class, 'cart'])->name('requisitions.cart');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

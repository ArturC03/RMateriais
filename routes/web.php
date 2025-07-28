<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfessorRequestController;
use App\Http\Middleware\EnsureUserIsProfessor;
use App\Models\Material;
use App\Models\RequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Http\Controllers\RequisitionController;
use \App\Http\Controllers\MaterialsController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware('auth', 'verified', EnsureUserIsProfessor::class)->name('dashboard');
Route::post('dashboard', [DashboardController::class, 'index'])->middleware('auth', 'verified', EnsureUserIsProfessor::class)->name('dashboard.post');
Route::get('dashboard/materiais', [\App\Http\Controllers\DashboardMaterialsController::class, 'index'])->middleware('auth', 'verified', EnsureUserIsProfessor::class)->name('dashboard.materials');
Route::get('dashboard/requisicoes', [ProfessorRequestController::class, 'index'])->middleware('auth', 'verified', EnsureUserIsProfessor::class)->name('dashboard.requests');

// Professor request management routes
Route::prefix('dashboard/requisicoes')->middleware(['auth', 'verified', EnsureUserIsProfessor::class])->group(function () {
    Route::get('/{request}', [ProfessorRequestController::class, 'show'])->name('dashboard.requests.show');
    Route::post('/confirm', [ProfessorRequestController::class, 'confirm'])->name('dashboard.requests.confirm');
    Route::post('/return', [ProfessorRequestController::class, 'markAsReturned'])->name('dashboard.requests.return');
    Route::post('/cancel', [ProfessorRequestController::class, 'cancel'])->name('dashboard.requests.cancel');
});

// Dashboard categories for chart filters
Route::get('/dashboard/categories', function () {
    return \App\Models\Category::select('id', 'name')->orderBy('name')->get();
})->middleware('auth', 'verified', EnsureUserIsProfessor::class);

Route::post('materiais/adicionar-ao-carrinho', [MaterialsController::class, 'addToCart'])
    ->middleware(['auth', 'verified'])->name('materials.cart.remove');

Route::post('materiais/remover-do-carrinho', [MaterialsController::class, 'removeFromCart'])
    ->middleware(['auth', 'verified'])->name('materials.cart.remove');

Route::post('requisicao/fazer-pedido', [RequisitionController::class, 'placeOrder'])
    ->middleware(['auth', 'verified'])->name('materials.cart.place_order');

Route::get('/catalogo', [RequisitionController::class, 'index'])->name('requisitions.index');
Route::get('/carrinho', [RequisitionController::class, 'cart'])->name('requisitions.cart');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

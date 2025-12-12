<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\SharedCartController;
use App\Http\Controllers\Api\DepartureController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\FavoriteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route de test API
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

// ==================== ROUTES PUBLIQUES ====================

// Authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Produits publics
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');
Route::get('/products/slug/{slug}', [ProductController::class, 'showBySlug'])->name('api.products.show-by-slug');
Route::get('/products/search/{query}', [ProductController::class, 'search'])->name('api.products.search');

// Catégories publiques
Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('api.categories.show');
Route::get('/categories/{id}/products', [CategoryController::class, 'products'])->name('api.categories.products');

// Régions publiques
Route::get('/regions', [RegionController::class, 'index'])->name('api.regions.index');
Route::get('/regions/{id}', [RegionController::class, 'show'])->name('api.regions.show');
Route::get('/regions/{id}/products', [RegionController::class, 'products'])->name('api.regions.products');

// Panier public (sessions)
Route::get('/cart/{type}', [CartController::class, 'show'])->name('api.cart.show');
Route::post('/cart/add', [CartController::class, 'add'])->name('api.cart.add');
Route::put('/cart/{id}', [CartController::class, 'update'])->name('api.cart.update');
Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('api.cart.remove');
Route::delete('/cart/clear/{type}', [CartController::class, 'clear'])->name('api.cart.clear');
Route::put('/cart/{id}/weight', [CartController::class, 'updateWeight'])->name('api.cart.update-weight');

// Paniers partagés publics
Route::get('/shared-carts/{token}', [SharedCartController::class, 'show'])->name('api.shared-carts.show');

// ==================== ROUTES PROTÉGÉES ====================

Route::middleware(['auth:sanctum'])->group(function () {
    
    // ========== AUTHENTIFICATION ==========
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::post('/refresh-token', [AuthController::class, 'refresh'])->name('api.refresh-token');

    // ========== PROFIL UTILISATEUR ==========
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('api.profile.update-password');

    // ========== FAVORIS ==========
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('api.favorites.index');
    Route::post('/favorites/{productId}', [FavoriteController::class, 'toggle'])->name('api.favorites.toggle');
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'remove'])->name('api.favorites.remove');

    // ========== ADRESSES ==========
    Route::get('/addresses', [AddressController::class, 'index'])->name('api.addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('api.addresses.store');
    Route::get('/addresses/{id}', [AddressController::class, 'show'])->name('api.addresses.show');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])->name('api.addresses.update');
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->name('api.addresses.destroy');

    // ========== COMMANDES ==========
    Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('api.orders.show');
    Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice'])->name('api.orders.invoice');

    // ========== PAIEMENT ==========
    Route::post('/payment/create-session', [PaymentController::class, 'createCheckoutSession'])->name('api.payment.create-session');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('api.payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('api.payment.cancel');
    Route::post('/payment/verify/{orderId}', [PaymentController::class, 'verifyPayment'])->name('api.payment.verify');

    // ========== PANIERS PARTAGÉS ==========
    Route::post('/shared-carts', [SharedCartController::class, 'share'])->name('api.shared-carts.share');
    Route::get('/my-shared-carts', [SharedCartController::class, 'mySharedCarts'])->name('api.shared-carts.my-shared-carts');
    Route::post('/shared-carts/{token}/participate', [SharedCartController::class, 'participate'])->name('api.shared-carts.participate');
    Route::delete('/shared-carts/{id}', [SharedCartController::class, 'destroy'])->name('api.shared-carts.destroy');

    // ========== DÉPARTS ==========
    Route::get('/departures', [DepartureController::class, 'index'])->name('api.departures.index');
    Route::get('/departures/{id}', [DepartureController::class, 'show'])->name('api.departures.show');
    Route::post('/departures', [DepartureController::class, 'store'])->name('api.departures.store');
    Route::delete('/departures/{id}', [DepartureController::class, 'destroy'])->name('api.departures.destroy');
    Route::patch('/departures/{id}/status', [DepartureController::class, 'api.departures.updateStatus']);

    // ========== ENQUÊTES ==========
    Route::get('/surveys', [SurveyController::class, 'index'])->name('api.surveys.index');
    Route::get('/surveys/{id}', [SurveyController::class, 'show'])->name('api.surveys.show');
    Route::post('/surveys', [SurveyController::class, 'store'])->name('api.surveys.store');

    // ========== PRODUITS (actions utilisateur) ==========
    Route::post('/products/{id}/rate', [ProductController::class, 'rate'])->name('api.products.rate');
    Route::post('/products/{id}/review', [ProductController::class, 'addReview'])->name('api.products.add-review');

});

// ==================== ROUTES ADMIN (si besoin) ====================

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Statistiques admin
    Route::get('/stats/overview', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => \App\Models\User::count(),
                'total_products' => \App\Models\Product::count(),
                'total_orders' => \App\Models\Order::count(),
                'total_revenue' => \App\Models\Order::where('is_paid', true)->sum('total')
            ]
        ]);
    })->name('api.admin.stats.overview');

    // Gestion des produits admin
    Route::apiResource('/products', \App\Http\Controllers\Api\Admin\ProductController::class)->names([
        'index' => 'api.admin.products.index',
        'store' => 'api.admin.products.store',
        'show' => 'api.admin.products.show',
        'update' => 'api.admin.products.update',
        'destroy' => 'api.admin.products.destroy'
    ]);
    
    Route::apiResource('/categories', \App\Http\Controllers\Api\Admin\CategoryController::class)->names([
        'index' => 'api.admin.categories.index',
        'store' => 'api.admin.categories.store',
        'show' => 'api.admin.categories.show',
        'update' => 'api.admin.categories.update',
        'destroy' => 'api.admin.categories.destroy'
    ]);
    
    Route::apiResource('/regions', \App\Http\Controllers\Api\Admin\RegionController::class)->names([
        'index' => 'api.admin.regions.index',
        'store' => 'api.admin.regions.store',
        'show' => 'api.admin.regions.show',
        'update' => 'api.admin.regions.update',
        'destroy' => 'api.admin.regions.destroy'
    ]);

    // Gestion des commandes admin
    Route::get('/orders', [\App\Http\Controllers\Api\Admin\OrderController::class, 'index'])->name('api.admin.orders.index');
    Route::get('/orders/{id}', [\App\Http\Controllers\Api\Admin\OrderController::class, 'show'])->name('api.admin.orders.show');
    Route::put('/orders/{id}/status', [\App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus'])->name('api.admin.orders.update-status');

});

// ==================== ROUTES DE FALLBACK ====================

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route API non trouvée'
    ], 404);
});
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
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/slug/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/search/{query}', [ProductController::class, 'search']);

// Catégories publiques
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{id}/products', [CategoryController::class, 'products']);

// Régions publiques
Route::get('/regions', [RegionController::class, 'index']);
Route::get('/regions/{id}', [RegionController::class, 'show']);
Route::get('/regions/{id}/products', [RegionController::class, 'products']);

// Panier public (sessions)
Route::get('/cart/{type}', [CartController::class, 'show']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::put('/cart/{id}', [CartController::class, 'update']);
Route::delete('/cart/{id}', [CartController::class, 'remove']);
Route::delete('/cart/clear/{type}', [CartController::class, 'clear']);
Route::put('/cart/{id}/weight', [CartController::class, 'updateWeight']);

// Paniers partagés publics
Route::get('/shared-carts/{token}', [SharedCartController::class, 'show']);

// ==================== ROUTES PROTÉGÉES ====================

Route::middleware(['auth:sanctum'])->group(function () {
    
    // ========== AUTHENTIFICATION ==========
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);

    // ========== PROFIL UTILISATEUR ==========
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // ========== FAVORIS ==========
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{productId}', [FavoriteController::class, 'toggle']);
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'remove']);

    // ========== ADRESSES ==========
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

    // ========== COMMANDES ==========
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice']);

    // ========== PAIEMENT ==========
    Route::post('/payment/create-session', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/payment/success', [PaymentController::class, 'success']);
    Route::get('/payment/cancel', [PaymentController::class, 'cancel']);
    Route::post('/payment/verify/{orderId}', [PaymentController::class, 'verifyPayment']);

    // ========== PANIERS PARTAGÉS ==========
    Route::post('/shared-carts', [SharedCartController::class, 'share']);
    Route::get('/my-shared-carts', [SharedCartController::class, 'mySharedCarts']);
    Route::post('/shared-carts/{token}/participate', [SharedCartController::class, 'participate']);
    Route::delete('/shared-carts/{id}', [SharedCartController::class, 'destroy']);

    // ========== DÉPARTS ==========
    Route::get('/departures', [DepartureController::class, 'index']);
    Route::get('/departures/{id}', [DepartureController::class, 'show']);
    Route::post('/departures', [DepartureController::class, 'store']);
    Route::delete('/departures/{id}', [DepartureController::class, 'destroy']);

    // ========== ENQUÊTES ==========
    Route::get('/surveys', [SurveyController::class, 'index']);
    Route::get('/surveys/{id}', [SurveyController::class, 'show']);
    Route::post('/surveys', [SurveyController::class, 'store']);

    // ========== PRODUITS (actions utilisateur) ==========
    Route::post('/products/{id}/rate', [ProductController::class, 'rate']);
    Route::post('/products/{id}/review', [ProductController::class, 'addReview']);

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
    });

    // Gestion des produits admin
    Route::apiResource('/products', \App\Http\Controllers\Api\Admin\ProductController::class);
    Route::apiResource('/categories', \App\Http\Controllers\Api\Admin\CategoryController::class);
    Route::apiResource('/regions', \App\Http\Controllers\Api\Admin\RegionController::class);
    
    // Gestion des commandes admin
    Route::get('/orders', [\App\Http\Controllers\Api\Admin\OrderController::class, 'index']);
    Route::get('/orders/{id}', [\App\Http\Controllers\Api\Admin\OrderController::class, 'show']);
    Route::put('/orders/{id}/status', [\App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus']);

});

// ==================== ROUTES DE FALLBACK ====================

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route API non trouvée'
    ], 404);
});
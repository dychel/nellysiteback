<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountPasswordController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminRegionController;
use App\Http\Controllers\Admin\AdminAddressController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminSharedCartController;
use App\Http\Controllers\Admin\AdminDepartureController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSurveyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MaRegionController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SharedCartController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Page d'accueil redirige vers le login
Route::get('/', function () {
    return redirect()->route('login');
});


// Authentification - PREMIÈRE PAGE
Route::get('/connexion', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/connexion', [LoginController::class, 'login']);
Route::post('/deconnexion', [LoginController::class, 'logout'])->name('logout');
Route::get('/inscription', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/inscription', [RegisterController::class, 'register']);

// Pages publiques
Route::get('/accueil', [HomeController::class, 'index'])->name('home');
Route::get('/produits', [ProductController::class, 'index'])->name('web.products.index');
Route::get('/produit/{slug}', [ProductController::class, 'show'])->name('web.products.show');

// Panier
Route::get('/panier/individuel', [CartController::class, 'individuel'])->name('cart.individuel');
Route::get('/panier/collectif', [CartController::class, 'collectif'])->name('cart.collectif');
Route::get('/cart/add/{id}/{type}', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart/delete/{id}/{type}', [CartController::class, 'delete'])->name('cart.delete');
Route::get('/cart/increase/{id}/{type}', [CartController::class, 'increase'])->name('cart.increase');
Route::get('/cart/decrease/{id}/{type}', [CartController::class, 'decrease'])->name('cart.decrease');
Route::get('/cart/increase-weight/{id}/{type}', [CartController::class, 'increaseWeight'])->name('cart.increase-weight');
Route::get('/cart/decrease-weight/{id}/{type}', [CartController::class, 'decreaseWeight'])->name('cart.decrease-weight');

// Régions
Route::prefix('ma-region')->group(function () {
    Route::get('/extreme-nord', [MaRegionController::class, 'extreme_nord'])->name('extreme_north');
    Route::get('/nord', [MaRegionController::class, 'nord'])->name('north');
    Route::get('/adamaoua', [MaRegionController::class, 'adamaoua'])->name('adamaoua');
    Route::get('/centre', [MaRegionController::class, 'centre'])->name('center');
    Route::get('/nord-ouest', [MaRegionController::class, 'nord_ouest'])->name('north_west');
    Route::get('/ouest', [MaRegionController::class, 'ouest'])->name('west');
    Route::get('/sud-ouest', [MaRegionController::class, 'sud_ouest'])->name('south_west');
    Route::get('/littoral', [MaRegionController::class, 'littoral'])->name('littoral');
    Route::get('/sud', [MaRegionController::class, 'sud'])->name('south');
    Route::get('/est', [MaRegionController::class, 'est'])->name('east');
    Route::get('/favori/toggle/{id}', [MaRegionController::class, 'toggleFavori'])->name('toggle_favori_region');
});

// Routes authentifiées
Route::middleware(['auth'])->group(function () {
    // Compte
    Route::get('/compte', [AccountController::class, 'index'])->name('account');
    Route::get('/compte/commande/{id}', [AccountController::class, 'show'])->name('account.order');
    Route::get('/compte/modifier-mot-de-passe', [AccountPasswordController::class, 'index'])->name('account.password');
    Route::post('/compte/modifier-mot-de-passe', [AccountPasswordController::class, 'update']);
    
    // Favoris
    Route::get('/mes-favoris', [ProductController::class, 'favoris'])->name('favoris');
    Route::get('/favori/toggle/{id}', [ProductController::class, 'toggleFavori'])->name('toggle_favori');
    
    // Commandes
    Route::get('/commande', [OrderController::class, 'index'])->name('order.index');
    Route::post('/commande/valider', [OrderController::class, 'validateOrder'])->name('order.validate');
    Route::get('/commande/collectif', [OrderController::class, 'collectif'])->name('order.collectif');
    Route::post('/commande/collectif/valider', [OrderController::class, 'validateCollectif'])->name('order.validate-collectif');
    
    // Paiement
    Route::get('/commande/paiement/{orderId}', [PaymentController::class, 'index'])->name('payment.index');
    Route::get('/commande/merci/{stripe_session_id}', [PaymentController::class, 'success'])->name('payment.success');
    
    // Départ
    Route::get('/preparer-depart', [DepartureController::class, 'index'])->name('departure.index');
    Route::post('/preparer-depart', [DepartureController::class, 'store'])->name('departure.store');
    
    // Panier partagé
    Route::get('/cart/share', [SharedCartController::class, 'share'])->name('cart.share');
    Route::get('/cart/shared/{token}', [SharedCartController::class, 'viewSharedCart'])->name('shared-cart.view');
    Route::post('/order/collectif/payment', [SharedCartController::class, 'paymentCollectif'])->name('shared-cart.payment');
    Route::post('/order/collectif/validate', [SharedCartController::class, 'validateCollectif'])->name('shared-cart.validate');
    
    // Enquête
    Route::get('/survey', [SurveyController::class, 'showForm'])->name('survey.index');
    Route::post('/survey', [SurveyController::class, 'store'])->name('survey.store');
});

// Administration
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - PAGE APRÈS CONNEXION ADMIN
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Produits
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.delete');
    
    // Catégories
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.delete');
    
    // Régions
    Route::get('/regions', [AdminRegionController::class, 'index'])->name('regions.index');
    Route::post('/regions', [AdminRegionController::class, 'store'])->name('regions.store');
    Route::put('/regions/{region}', [AdminRegionController::class, 'update'])->name('regions.update');
    Route::delete('/regions/{region}', [AdminRegionController::class, 'destroy'])->name('regions.delete');
    
    // Adresses
    Route::get('/addresses', [AdminAddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AdminAddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AdminAddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AdminAddressController::class, 'destroy'])->name('addresses.delete');
    
    // Commandes
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    
    // Paniers collectifs
    Route::get('/shared-carts', [AdminSharedCartController::class, 'index'])->name('shared-carts.index');
    Route::get('/shared-carts/{sharedCart}', [AdminSharedCartController::class, 'show'])->name('shared-carts.show');
    
    // Départs
    Route::get('/departures', [AdminDepartureController::class, 'index'])->name('departures.index');
    Route::get('/departures/{departure}', [AdminDepartureController::class, 'show'])->name('departures.show');
    Route::delete('/departures/{departure}', [AdminDepartureController::class, 'destroy'])->name('departures.delete');
    
    // Utilisateurs
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
    
    // Enquêtes
    Route::get('/surveys', [AdminSurveyController::class, 'index'])->name('surveys.index');
    Route::get('/surveys/{survey}', [AdminSurveyController::class, 'show'])->name('surveys.show');
    Route::get('/surveys/stats', [AdminSurveyController::class, 'stats'])->name('surveys.stats');
    
    // Statistiques (anciennes - gardées pour compatibilité)
    Route::get('/stats', [StatsController::class, 'index'])->name('stats');
    Route::get('/diagrammes-en-baton', [StatsController::class, 'dia_bat'])->name('dia_bat');
    Route::get('/camembert', [StatsController::class, 'camembert'])->name('camembert');
    
    // Gestion (ancienne - gardée pour compatibilité)
    Route::get('/gestion', [ManagementController::class, 'index'])->name('management');

    Route::get('/img/{filename}', function($filename) {
    $path = public_path('uploads/' . $filename);
    if (file_exists($path)) {
        return response()->file($path);
        }
        abort(404);
    });
});

// Home favori toggle
Route::get('/favori/toggle/{id}', [HomeController::class, 'toggleFavori'])->name('home.toggle_favori');
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AuthController;

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

// Routes publiques
Route::post('v1/login', [AuthController::class, 'login']);
Route::post('v1/refresh', [AuthController::class, 'refreshToken']);

// Route::post('v1/users', [UserController::class, 'store']);

// Routes protégées
Route::middleware(['auth:sanctum', /*'checkToken'*/])->group(function () {
    // Route de déconnexion
    Route::post('v1/logout', [AuthController::class, 'logout']);

    // Routes pour les utilisateurs
    Route::prefix('v1/users')->as('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::patch('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Routes pour les clients
    Route::prefix('v1/clients')->as('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/{id}', [ClientController::class, 'show'])->name('show');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::patch('/{client}', [ClientController::class, 'update'])->name('update');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
    });

    // Routes pour les articles
    Route::prefix('v1/articles')->as('articles.')->group(function () {
        Route::get('/', [ArticleController::class, 'index'])->name('index');
        Route::get('/{id}', [ArticleController::class, 'show'])->name('show');
        Route::post('/', [ArticleController::class, 'store'])->name('store');
        Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy');
        Route::get('/trashed', [ArticleController::class, 'trashed'])->name('trashed');
        Route::post('/{id}/restore', [ArticleController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [ArticleController::class, 'forceDelete'])->name('forceDelete');
        Route::post('/stock', [ArticleController::class, 'updateStock'])->name('updateStock');
        // Route::patch('/{id}', [ArticleController::class, 'updateOrUpdateStock'])->name('updateOrUpdateStock');
        Route::patch('/{id}', [ArticleController::class, 'updateStockSingle'])->name('updateStockSingle');
        Route::post('/libelle', [ArticleController::class, 'searchByLibelle'])->name('searchByLibelle');

    });

    // Routes pour les catégoriess
    Route::prefix('v1/categories')->as('categories.')->group(function () {
        Route::get('/', [CategorieController::class, 'index'])->name('index');
        Route::get('/{id}', [CategorieController::class, 'show'])->name('show');
        Route::post('/', [CategorieController::class, 'store'])->name('store');
        Route::patch('/{categorie}', [CategorieController::class, 'update'])->name('update');
        Route::delete('/{categorie}', [CategorieController::class, 'destroy'])->name('destroy');
    });

    // Routes pour les promos
    Route::prefix('v1/promos')->as('promos.')->group(function () {
        Route::get('/', [PromoController::class, 'index'])->name('index');
        Route::get('/{id}', [PromoController::class, 'show'])->name('show');
        Route::post('/', [PromoController::class, 'store'])->name('store');
        Route::patch('/{promo}', [PromoController::class, 'update'])->name('update');
        Route::delete('/{promo}', [PromoController::class, 'destroy'])->name('destroy');
    });

    // Route pour obtenir les informations de l'utilisateur connecté
    Route::get('/v1/user', function (Request $request) {
        return $request->user();
    });
});



Route::prefix('v1/clients')->as('clients.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::get('/{id}', [ClientController::class, 'show'])->name('show');
    Route::post('/', [ClientController::class, 'store'])->name('store');
    Route::patch('/{client}', [ClientController::class, 'update'])->name('update');
    Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
});


Route::prefix('v1/users')->as('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{id}', [UserController::class, 'show'])->name('show');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::patch('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});


// Routes pour les articles (middleware commenté pour permettre les tests)
// Route::prefix('v1/articles')->as('articles.')->group(function () {
//     Route::get('/', [ArticleController::class, 'index'])->name('index');
//     Route::get('/{id}', [ArticleController::class, 'show'])->name('show');
//     Route::post('/', [ArticleController::class, 'store'])->name('store');
//     Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy');
//     Route::get('/trashed', [ArticleController::class, 'trashed'])->name('trashed');
//     Route::post('/{id}/restore', [ArticleController::class, 'restore'])->name('restore');
//     Route::delete('/{id}/force', [ArticleController::class, 'forceDelete'])->name('forceDelete');
//     Route::post('/stock', [ArticleController::class, 'updateStock'])->name('updateStock');
//     // Route::patch('/{id}', [ArticleController::class, 'updateOrUpdateStock'])->name('updateOrUpdateStock');
//     Route::patch('/{id}', [ArticleController::class, 'updateStockSingle'])->name('updateStockSingle');
//     Route::post('/libelle', [ArticleController::class, 'searchByLibelle'])->name('searchByLibelle');

// });

// Fallback route pour les routes non définies
Route::fallback(function () {
    return response()->json(['message' => 'Page non trouvée !'], 404);
});

// JSON pour creer un nouvel utilisateur
// {
//     "nom": "GUEYE",
//     "prenom": "MAMADOU",
//     "email": "jpapa@gmail.com",
//     "mot_de_passe": "Admin123@",
//     "role": "ADMIN"
// }

// {
//     "email": "jpapa@gmail.com",
//     "mot_de_passe": "Admin123@"
// }

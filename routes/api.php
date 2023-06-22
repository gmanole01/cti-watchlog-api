<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MoviesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'middleware' => 'api'
], function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('me', [AuthController::class, 'me']);

    // movies
    Route::prefix('movies')->group(function() {
        Route::post('discover', [MoviesController::class, 'discover']);
        Route::post('top_rated', [MoviesController::class, 'topRated']);
        Route::post('genres', [MoviesController::class, 'genres']);

        Route::post('get/{id}', [MoviesController::class, 'get']);

        Route::prefix('favourites')->group(function() {
            Route::post('all', [\App\Http\Controllers\FavouriteMoviesController::class, 'all']);
            Route::post('add', [\App\Http\Controllers\FavouriteMoviesController::class, 'add']);
        });

        Route::prefix('watched')->group(function() {
            Route::post('all', [\App\Http\Controllers\WatchedMoviesController::class, 'all']);
            Route::post('add', [\App\Http\Controllers\WatchedMoviesController::class, 'add']);
        });
    });

    // shows

    Route::prefix('friends')->group(function() {
        Route::post('all', [\App\Http\Controllers\FriendsController::class, 'all']);
        Route::post('requests', [\App\Http\Controllers\FriendsController::class, 'requests']);
        Route::post('accept', [\App\Http\Controllers\FriendsController::class, 'acceptRequest']);
        Route::post('refuse', [\App\Http\Controllers\FriendsController::class, 'refuseRequest']);
        Route::post('add', [\App\Http\Controllers\FriendsController::class, 'add']);
    });

});

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminAuthController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Laravel connected!'
    ]);
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello Laravel!'
    ]);
});
// Route::get('/clothes', [ClothController::class, 'index']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{user}', [UserController::class, 'update']);
Route::delete('/users/{user}', [UserController::class, 'destroy']);

Route::middleware('web')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/user', [UserController::class, 'me']);
    });
});

Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{blog}', [BlogController::class, 'show']);
Route::post('/blogs', [BlogController::class, 'store']);
Route::put('/blogs/{blog}', [BlogController::class, 'update']);
Route::delete('/blogs/{blog}', [BlogController::class, 'destroy']);

// Route::post('/admin/login', [AdminAuthController::class, 'login']);
?>
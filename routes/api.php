<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;

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

// ログイン系
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/blogs', [BlogController::class, 'store']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user', [UserController::class, 'me']);

    Route::post('/blogs/{blog}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/blogs/{blog}/favorite', [FavoriteController::class, 'destroy']);

    Route::get('/blogs/{blog}/comments', [CommentController::class, 'index']);
    Route::post('/blogs/{blog}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});

Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{blog}', [BlogController::class, 'show']);
// Route::post('/blogs', [BlogController::class, 'store']);
Route::put('/blogs/{blog}', [BlogController::class, 'update']);
Route::delete('/blogs/{blog}', [BlogController::class, 'destroy']);

// Route::post('/admin/login', [AdminAuthController::class, 'login']);
?>
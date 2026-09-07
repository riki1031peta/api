<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
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

Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{blog}', [BlogController::class, 'show']);
Route::post('/blogs', [BlogController::class, 'store']);
Route::put('/blogs/{blog}', [BlogController::class, 'update']);
Route::delete('/blogs/{blog}', [BlogController::class, 'destroy']);

Route::post('/admin/login', [AdminAuthController::class, 'login']);
?>
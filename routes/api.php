<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\BlogImageController;
use App\Http\Controllers\LineNotificationController;
use App\Http\Controllers\LineAuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;

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

Route::get('/line/callback', [LineAuthController::class, 'callback']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/line/test', [LineNotificationController::class, 'test']);
    Route::get('/line/connect', [LineAuthController::class, 'connect']);

    Route::post('/blogs', [BlogController::class, 'store']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user', [UserController::class, 'me']);

    Route::post('/blogs/{blog}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/blogs/{blog}/favorite', [FavoriteController::class, 'destroy']);

    Route::get('/blogs/{blog}/comments', [CommentController::class, 'index']);
    Route::post('/blogs/{blog}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::get('/notifications', function (Request $request) {
        return $request->user()
            ->notifications()
            ->latest()
            ->get();
    });

    Route::get('/notifications/unread', function (Request $request) {
        return $request->user()
            ->unreadNotifications()
            ->latest()
            ->get();
    });

    Route::post('/notifications/read-all', function (Request $request) {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return response()->json([
            'message' => '通知を既読にしました。',
        ]);
    });

    Route::post('/blog-images', [BlogImageController::class, 'store']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{blog}', [BlogController::class, 'show']);
// Route::post('/blogs', [BlogController::class, 'store']);
Route::put('/blogs/{blog}', [BlogController::class, 'update']);
Route::delete('/blogs/{blog}', [BlogController::class, 'destroy']);

Route::get('/sitemap-routes', [SitemapController::class, 'index']);

// Route::post('/admin/login', [AdminAuthController::class, 'login']);
?>
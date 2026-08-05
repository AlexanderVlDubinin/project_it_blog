<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('verified')->group(function () {
        Route::resource('posts', PostController::class)->middleware('verified');
        /*
        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('index');
            Route::get('/create', [PostController::class, 'create'])->name('create');
            Route::get('/{post}', [PostController::class, 'show'])->name('show');
            Route::get('/{post}/edit', [PostController::class, 'edit'])->name('edit');
            Route::post('/', [PostController::class, 'store'])->name('store');
            Route::patch('/{post}', [PostController::class, 'update'])->name('update');
            Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');
        });
        */

        Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');

        Route::middleware('role:admin,moderator')->prefix('admin')->name('admin.')->group(function () {
            Route::put('/comments/{comment}/delete', [CommentController::class, 'delete'])->name('comments.delete');
            Route::put('/comments/{comment}/restore', [CommentController::class, 'restore'])->name('comments.restore');
            Route::delete('/comments/{comment}/destroy', [CommentController::class, 'forceDelete'])->name('comments.destroy');
        });

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{id}/read', [NotificationController::class, 'readAndRedirect'])->name('notifications.read');
        Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        Route::delete('/notifications/delete-all-read', [NotificationController::class, 'deleteAllRead'])->name('notifications.deleteAllRead');
    });
});

require __DIR__.'/auth.php';

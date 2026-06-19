<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\AboutUsController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\HomeController;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


// Redirects
Route::redirect('admin/login', '/login');
Route::redirect('/dashboard', '/admin');
Route::redirect('admin/dashboard', '/admin');

// Fallback
Route::fallback(function () {
    return Inertia::render('Error/Index', [
        'status' => 404,
        'statusText' => 'Page Not Found',
        'message' => 'Sorry, the page you are looking for could not be found.',
    ]);
});


Route::middleware(['web'])->group(
    function () {
        // Home
        Route::get('/', [HomeController::class, 'index'])->name('home');

        // Blogs
        Route::get('/blog', [BlogController::class, 'index'])->name('blog');
        Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

        // AboutUs
        Route::get('/about', [AboutUsController::class, 'index'])->name('about');
    }
);

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__ . '/auth.php';

<?php

use App\Http\Controllers\ProfileController;
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
        Route::get('/', [HomeController::class, 'index'])->name('home');
    }
);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__ . '/auth.php';

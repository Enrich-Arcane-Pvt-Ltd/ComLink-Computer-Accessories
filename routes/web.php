<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\AboutUsController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\CustomPagesController;
use App\Http\Controllers\Public\FAQController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsletterController;
use App\Http\Controllers\Public\TestimonialController;
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

        // FAQs
        Route::get('/faqs', [FAQController::class, 'index'])->name('faqs');

        // Testimonials
        Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials');

        // ContactUs
        Route::get('/contact', [ContactController::class, 'index'])->name('contact');
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

        // Legal Pages
        Route::get('/terms-of-service', [CustomPagesController::class, 'terms'])->name('terms.show');
        Route::get('/privacy-policy', [CustomPagesController::class, 'privacy'])->name('privacy.show');
        Route::get('/shipping-delivery', [CustomPagesController::class, 'shipping'])->name('shipping.show');
        Route::get('/return-refund', [CustomPagesController::class, 'refunds'])->name('refunds.show');

        // Newsletter
        Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->name('newsletter.subscribe');
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

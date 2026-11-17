@
<?php

use App\Http\Controllers\AdminBusinessReportController;
use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BusinessDirectoryController;
use App\Http\Controllers\BusinessProductController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\BusinessReviewReplyController;
use App\Http\Controllers\CustomerBusinessReportController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::post('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
        ->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('google.callback');
});

// Protected dashboard.
Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/directory', [BusinessDirectoryController::class, 'index'])
        ->name('directory.index');
    Route::get('/directory/{businessProfile}', [BusinessDirectoryController::class, 'show'])
        ->whereNumber('businessProfile')
        ->name('directory.show');
    Route::post('/directory/{businessProfile}/reviews', [CustomerReviewController::class, 'store'])
        ->whereNumber('businessProfile')
        ->name('reviews.store');
    Route::post('/directory/{businessProfile}/reports', [CustomerBusinessReportController::class, 'store'])
        ->whereNumber('businessProfile')
        ->middleware('throttle:5,1')
        ->name('business-reports.store');
    Route::get('/my-reports', [CustomerBusinessReportController::class, 'index'])
        ->name('business-reports.mine');
    Route::get('/favourites', [FavouriteController::class, 'index'])
        ->name('favourites.index');
    Route::post('/directory/{businessProfile}/favourite', [FavouriteController::class, 'toggle'])
        ->whereNumber('businessProfile')
        ->name('favourites.toggle');
    Route::put('/reviews/{review}', [CustomerReviewController::class, 'update'])
        ->whereNumber('review')
        ->name('reviews.update');
    Route::delete('/reviews/{review}', [CustomerReviewController::class, 'destroy'])
        ->whereNumber('review')
        ->name('reviews.destroy');

    Route::get('/business-profile', [BusinessProfileController::class, 'show'])
        ->name('business-profile.show');
    Route::get('/business-profile/create', [BusinessProfileController::class, 'create'])
        ->name('business-profile.create');
    Route::post('/business-profile', [BusinessProfileController::class, 'store'])
        ->name('business-profile.store');
    Route::get('/business-profile/edit', [BusinessProfileController::class, 'edit'])
        ->name('business-profile.edit');
    Route::put('/business-profile', [BusinessProfileController::class, 'update'])
        ->name('business-profile.update');

    Route::resource('business-products', BusinessProductController::class)
        ->except(['show'])
        ->parameters(['business-products' => 'product']);

    Route::get('/business-reviews', [BusinessReviewReplyController::class, 'index'])
        ->name('business-reviews.index');
    Route::put('/business-reviews/{review}/reply', [BusinessReviewReplyController::class, 'saveReply'])
        ->whereNumber('review')
        ->name('business-reviews.reply');
    Route::delete('/business-reviews/{review}/reply', [BusinessReviewReplyController::class, 'destroyReply'])
        ->whereNumber('review')
        ->name('business-reviews.reply.destroy');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'account.active', 'admin'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profiles', [AdminDashboardController::class, 'profiles'])->name('profiles.index');
        Route::get('/profiles/{businessProfile}', [AdminDashboardController::class, 'showProfile'])
            ->name('profiles.show');
        Route::post('/profiles/{businessProfile}/approve', [AdminDashboardController::class, 'approve'])
            ->name('profiles.approve');
        Route::post('/profiles/{businessProfile}/reject', [AdminDashboardController::class, 'reject'])
            ->name('profiles.reject');

        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [AdminReviewController::class, 'show'])
            ->whereNumber('review')
            ->name('reviews.show');
        Route::patch('/reviews/{review}', [AdminReviewController::class, 'update'])
            ->whereNumber('review')
            ->name('reviews.update');
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])
            ->whereNumber('review')
            ->name('reviews.destroy');
        Route::patch('/review-replies/{reviewReply}', [AdminReviewController::class, 'updateReply'])
            ->whereNumber('reviewReply')
            ->name('review-replies.update');
        Route::delete('/review-replies/{reviewReply}', [AdminReviewController::class, 'destroyReply'])
            ->whereNumber('reviewReply')
            ->name('review-replies.destroy');

        Route::get('/reports', [AdminBusinessReportController::class, 'index'])->name('business-reports.index');
        Route::get('/reports/{businessReport}', [AdminBusinessReportController::class, 'show'])
            ->whereNumber('businessReport')
            ->name('business-reports.show');
        Route::patch('/reports/{businessReport}', [AdminBusinessReportController::class, 'update'])
            ->whereNumber('businessReport')
            ->name('business-reports.update');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])
            ->whereNumber('user')
            ->name('users.show');
        Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])
            ->whereNumber('user')
            ->name('users.role.update');
        Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])
            ->whereNumber('user')
            ->name('users.status.update');

        Route::get('/categories', [AdminCatalogController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminCatalogController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCatalogController::class, 'updateCategory'])->name('categories.update');

        Route::get('/locations', [AdminCatalogController::class, 'locations'])->name('locations');
        Route::post('/local-government-areas', [AdminCatalogController::class, 'storeLocalGovernmentArea'])
            ->name('local-government-areas.store');
        Route::put('/local-government-areas/{localGovernmentArea}', [AdminCatalogController::class, 'updateLocalGovernmentArea'])
            ->name('local-government-areas.update');
        Route::post('/towns', [AdminCatalogController::class, 'storeTown'])->name('towns.store');
        Route::put('/towns/{town}', [AdminCatalogController::class, 'updateTown'])->name('towns.update');
    });

require __DIR__.'/settings.php';


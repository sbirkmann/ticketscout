<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;

Route::get('/', [\App\Http\Controllers\FrontendController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/events', [\App\Http\Controllers\FrontendController::class, 'events'])->name('events.index');
Route::get('/events/{slug}', [\App\Http\Controllers\FrontendController::class, 'showEvent'])->name('event.show');
Route::get('/locations', [FrontendController::class, 'locations'])->name('locations.index');
Route::get('/location/{slug}', [FrontendController::class, 'showLocation'])->name('locations.show');
Route::get('/orte', [FrontendController::class, 'cities'])->name('cities.index');
Route::get('/ort/{slug}', [FrontendController::class, 'showCity'])->name('cities.show');
Route::get('/category/{slug}', [FrontendController::class, 'showCategory'])->name('categories.show');
Route::get('/artists', [FrontendController::class, 'artists'])->name('artists.index');
Route::get('/artist/{slug}', [FrontendController::class, 'showArtist'])->name('artists.show');
Route::get('/event/{slug}', [FrontendController::class, 'showEvent'])->name('event.show');
Route::post('/event/{event}/waitlist', [\App\Http\Controllers\WaitlistController::class, 'store'])->name('event.waitlist');
Route::get('/event/{slug}/ics', [FrontendController::class, 'downloadIcs'])->name('event.ics');

Route::get('/checkout/{event:slug}', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/{event:slug}/validate-promo', [CheckoutController::class, 'validatePromo'])->name('checkout.validate-promo');
Route::post('/checkout/{event:slug}', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/{event}/complete', [\App\Http\Controllers\CheckoutController::class, 'complete'])->name('checkout.complete');
Route::get('/checkout/success', function () {
    return Inertia::render('Checkout/Success', ['order_id' => session('order_id')]);
})->name('checkout.success');
Route::post('/api/voucher/check', [\App\Http\Controllers\VoucherController::class, 'check'])->name('voucher.check');
Route::post('/voucher/checkout', [\App\Http\Controllers\VoucherController::class, 'checkout'])->name('voucher.checkout');
Route::get('/voucher/success', [\App\Http\Controllers\VoucherController::class, 'success'])->name('voucher.success');

Route::get('/impressum', function() { return Inertia::render('Static/Impressum'); })->name('impressum');
Route::get('/datenschutz', function() { return Inertia::render('Static/Datenschutz'); })->name('datenschutz');
Route::get('/agb', function() { return Inertia::render('Static/Agb'); })->name('agb');
Route::get('/hilfe', function() { return Inertia::render('Static/Hilfe'); })->name('hilfe');
Route::get('/gutscheine', function() { return Inertia::render('Static/Gutscheine'); })->name('gutscheine');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('superadmin')) {
        return redirect()->route('superadmin.dashboard');
    } elseif ($user->hasRole('vendor')) {
        return redirect()->route('vendor.dashboard');
    } elseif ($user->hasRole('scanner')) {
        return redirect()->route('scanner.dashboard');
    }
    return redirect()->route('customer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Superadmin\DashboardController::class, 'index'])->name('dashboard');

    // Approvals
    Route::post('/events/{event}/approve', [\App\Http\Controllers\Superadmin\DashboardController::class, 'approveEvent'])->name('events.approve');
    Route::post('/locations/{location}/approve', [\App\Http\Controllers\Superadmin\DashboardController::class, 'approveLocation'])->name('locations.approve');

    // Location Management
    Route::get('/locations', [\App\Http\Controllers\Superadmin\LocationController::class, 'index'])->name('locations.index');
    Route::post('/locations', [\App\Http\Controllers\Superadmin\LocationController::class, 'store'])->name('locations.store');
    Route::post('/locations/{location}', [\App\Http\Controllers\Superadmin\LocationController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{location}', [\App\Http\Controllers\Superadmin\LocationController::class, 'destroy'])->name('locations.destroy');
    Route::patch('/locations/{location}/toggle-global', [\App\Http\Controllers\Superadmin\LocationController::class, 'toggleGlobal'])->name('locations.toggle-global');

    // Event Management
    Route::get('/events', [\App\Http\Controllers\Superadmin\EventController::class, 'index'])->name('events.index');
    Route::delete('/events/{event}', [\App\Http\Controllers\Superadmin\EventController::class, 'destroy'])->name('events.destroy');

    // Orders Management
    Route::get('/orders', [\App\Http\Controllers\Superadmin\OrderController::class, 'index'])->name('orders.index');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Superadmin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Superadmin\SettingController::class, 'store'])->name('settings.store');
    
    // System Health
    Route::get('/health', [\App\Http\Controllers\Superadmin\HealthController::class, 'index'])->name('health.index');
    
    // Impersonation (Superadmin logs in as User)
    Route::post('/impersonate/{user}', [\App\Http\Controllers\Superadmin\ImpersonationController::class, 'impersonate'])->name('impersonate');
    
    // Seating Plans
    Route::get('/seating-plans', [\App\Http\Controllers\Superadmin\SeatingPlanController::class, 'index'])->name('seating-plans.index');
    Route::post('/seating-plans', [\App\Http\Controllers\Superadmin\SeatingPlanController::class, 'store'])->name('seating-plans.store');
    Route::post('/seating-plans/{seatingPlan}', [\App\Http\Controllers\Superadmin\SeatingPlanController::class, 'update'])->name('seating-plans.update');
    Route::delete('/seating-plans/{seatingPlan}', [\App\Http\Controllers\Superadmin\SeatingPlanController::class, 'destroy'])->name('seating-plans.destroy');

    // Cities (Orte)
    Route::get('/cities', [\App\Http\Controllers\Superadmin\CityController::class, 'index'])->name('cities.index');
    Route::post('/cities', [\App\Http\Controllers\Superadmin\CityController::class, 'store'])->name('cities.store');
    Route::post('/cities/{city}', [\App\Http\Controllers\Superadmin\CityController::class, 'update'])->name('cities.update');
    Route::delete('/cities/{city}', [\App\Http\Controllers\Superadmin\CityController::class, 'destroy'])->name('cities.destroy');
});

Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('dashboard');

    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\Vendor\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Vendor\InvoiceController::class, 'download'])->name('invoices.download');
    // Locations & Seating Plans
    Route::resource('locations.seating-plans', \App\Http\Controllers\Vendor\SeatingPlanController::class);

    // Event Management
    Route::post('events/ai-description', [\App\Http\Controllers\Vendor\EventController::class, 'generateAiDescription'])->name('events.ai-description');
    Route::post('events/{event}/duplicate', [\App\Http\Controllers\Vendor\EventController::class, 'duplicate'])->name('events.duplicate');
    Route::get('events/{event}/seating', [\App\Http\Controllers\Vendor\EventController::class, 'seating'])->name('events.seating');
    Route::put('events/{event}/seating', [\App\Http\Controllers\Vendor\EventController::class, 'updateSeating'])->name('events.seating.update');
    Route::post('/events/bulk', [\App\Http\Controllers\Vendor\EventController::class, 'bulk'])->name('events.bulk');
    Route::resource('events', \App\Http\Controllers\Vendor\EventController::class)->names('events');

    // Settings & Onboarding
    Route::get('/settings', [\App\Http\Controllers\Vendor\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Vendor\SettingsController::class, 'update'])->name('settings.update');

    // Ticket Templates
    Route::resource('templates', \App\Http\Controllers\Vendor\TicketTemplateController::class)->names('templates');

    // Orders
    Route::get('/orders', [\App\Http\Controllers\Vendor\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [\App\Http\Controllers\Vendor\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/resend', [\App\Http\Controllers\Vendor\OrderController::class, 'resendTickets'])->name('orders.resend');

    // Addon Management (nested under events)
    Route::post('/events/{event}/addons', [\App\Http\Controllers\Vendor\AddonController::class, 'store'])->name('events.addons.store');
    Route::put('/events/{event}/addons/{addon}', [\App\Http\Controllers\Vendor\AddonController::class, 'update'])->name('events.addons.update');
    Route::delete('/events/{event}/addons/{addon}', [\App\Http\Controllers\Vendor\AddonController::class, 'destroy'])->name('events.addons.destroy');

    // Ticket Categories
    Route::post('/events/{event}/ticket-categories', [\App\Http\Controllers\Vendor\TicketCategoryController::class, 'store'])->name('events.ticket-categories.store');
    Route::put('/events/{event}/ticket-categories/{category}', [\App\Http\Controllers\Vendor\TicketCategoryController::class, 'update'])->name('events.ticket-categories.update');
    Route::delete('/events/{event}/ticket-categories/{category}', [\App\Http\Controllers\Vendor\TicketCategoryController::class, 'destroy'])->name('events.ticket-categories.destroy');
    Route::patch('/events/{event}/ticket-categories/{category}/toggle-active', [\App\Http\Controllers\Vendor\TicketCategoryController::class, 'toggleActive'])->name('events.ticket-categories.toggle-active');

    // CRM / Newsletter
    Route::get('/crm', [\App\Http\Controllers\Vendor\CRMController::class, 'index'])->name('crm.index');
    Route::post('/crm/send', [\App\Http\Controllers\Vendor\CRMController::class, 'send'])->name('crm.send');

    // Echtzeit Check-in Tracker
    Route::get('/checkins', [\App\Http\Controllers\Vendor\CheckinController::class, 'index'])->name('checkins.index');

    // Promo Codes
    Route::resource('promo-codes', \App\Http\Controllers\Vendor\PromoCodeController::class)->except(['show']);
    
    // Staff / Scanner Accounts
    Route::resource('staff', \App\Http\Controllers\Vendor\StaffController::class)->only(['index', 'store', 'destroy']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('/events/{event}/favorite', [\App\Http\Controllers\Customer\FavoriteController::class, 'toggle'])->name('events.favorite');
    Route::get('/favorites', [\App\Http\Controllers\Customer\FavoriteController::class, 'index'])->name('favorites.index');
});

Route::middleware(['auth', 'role:vendor'])->group(function () {
    Route::get('/stripe/connect', [StripeController::class, 'connect'])->name('stripe.connect');
});

Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::middleware(['auth', 'role:scanner'])->prefix('scanner')->name('scanner.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Scanner\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/validate/{hash}', [\App\Http\Controllers\Scanner\DashboardController::class, 'validateTicket'])->name('validate');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Impersonation leave
    Route::post('/impersonate/leave', [\App\Http\Controllers\Superadmin\ImpersonationController::class, 'leave'])->name('impersonate.leave');
});

require __DIR__.'/auth.php';

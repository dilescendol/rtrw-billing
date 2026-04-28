<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenieacsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotspotUserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NasDeviceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RadiusServerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\Webhooks\PakasirWebhookController;
use Illuminate\Support\Facades\Route;

// ----- Public -----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');

// ----- Guest auth -----
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ----- Email verification -----
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/verify-email/resend', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')->name('verification.send');
});

// ----- Notifications (any authenticated user) -----
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
});

// ----- Subscription / suspended (auth + verified, but NOT tenant.usable) -----
Route::middleware(['auth'])->prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/suspended', [SubscriptionController::class, 'suspended'])->name('suspended');
    Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
    Route::post('/plans/{plan}/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::get('/plans/{plan}/return', [SubscriptionController::class, 'return'])->name('return');
});

// ----- Super Admin (platform-wide) -----
Route::middleware(['auth', 'role:superadmin,platform_admin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('tenants.index');
        Route::get('/plans', [SuperAdminController::class, 'plans'])->name('plans.index');
    });

// ----- Customer Portal -----
Route::middleware(['auth', 'customer.portal'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {
        Route::get('/', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/invoices', [CustomerPortalController::class, 'invoices'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [CustomerPortalController::class, 'invoiceShow'])->name('invoices.show');
        Route::get('/payments', [CustomerPortalController::class, 'payments'])->name('payments.index');
        Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
        Route::post('/profile', [CustomerPortalController::class, 'updateProfile'])->name('profile.update');
    });

// ----- Tenant App (admin / teknisi / kolektor) -----
Route::middleware(['auth', 'verified', 'tenant.usable', 'role:admin,owner,teknisi,kolektor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::resource('packages', PackageController::class)->except(['show']);

    Route::middleware('plan.feature:mikrotik')->group(function () {
        Route::resource('nas', NasDeviceController::class)->except(['show']);
        Route::post('/nas/{nas}/test', [NasDeviceController::class, 'test'])->name('nas.test');
    });

    Route::middleware('plan.feature:hotspot')->group(function () {
        Route::resource('hotspot', HotspotUserController::class)->except(['show']);
    });

    Route::middleware('plan.feature:voucher')->group(function () {
        Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
        Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
        Route::delete('/vouchers/batch', [VoucherController::class, 'destroyBatch'])->name('vouchers.batch.destroy');
        Route::get('/vouchers/print', [VoucherController::class, 'print'])->name('vouchers.print');
    });

    Route::middleware(['role:admin,owner', 'plan.feature:radius'])->group(function () {
        Route::resource('radius', RadiusServerController::class)->except(['show']);
        Route::post('/radius/{radius}/test', [RadiusServerController::class, 'test'])->name('radius.test');
    });

    Route::middleware(['role:admin,owner', 'plan.feature:genieacs'])->prefix('genieacs')->name('genieacs.')->group(function () {
        Route::get('/', [GenieacsController::class, 'index'])->name('index');
        Route::get('/create', [GenieacsController::class, 'create'])->name('create');
        Route::post('/', [GenieacsController::class, 'store'])->name('store');
        Route::get('/{genieacs}/edit', [GenieacsController::class, 'edit'])->name('edit');
        Route::put('/{genieacs}', [GenieacsController::class, 'update'])->name('update');
        Route::delete('/{genieacs}', [GenieacsController::class, 'destroy'])->name('destroy');
        Route::post('/{genieacs}/test', [GenieacsController::class, 'test'])->name('test');
        Route::get('/{genieacs}/devices', [GenieacsController::class, 'devices'])->name('devices');
        Route::get('/{genieacs}/devices/{deviceId}', [GenieacsController::class, 'deviceShow'])
            ->where('deviceId', '.*')->name('devices.show');
        Route::post('/{genieacs}/devices/{deviceId}/reboot', [GenieacsController::class, 'deviceReboot'])
            ->where('deviceId', '.*')->name('devices.reboot');
        Route::post('/{genieacs}/devices/{deviceId}/refresh', [GenieacsController::class, 'deviceRefresh'])
            ->where('deviceId', '.*')->name('devices.refresh');
    });

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::post('/invoices/generate-month', [InvoiceController::class, 'generateMonth'])->name('invoices.generate_month');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create/{invoice}', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/store/{invoice}', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::prefix('settings')->name('settings.')->middleware('role:admin,owner')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/business', [SettingsController::class, 'updateBusiness'])->name('business');
        Route::post('/pakasir', [SettingsController::class, 'updatePakasir'])->name('pakasir');
        Route::post('/mikrotik', [SettingsController::class, 'updateMikrotik'])->name('mikrotik');
        Route::post('/mikrotik/test', [SettingsController::class, 'testMikrotik'])->name('mikrotik.test');
        Route::post('/fonnte', [SettingsController::class, 'updateFonnte'])->name('fonnte');
        Route::post('/fonnte/test', [SettingsController::class, 'testFonnte'])->name('fonnte.test');
    });
});

// ----- Webhooks (public, signature-verified) -----
Route::post('/webhooks/pakasir/tenant/{tenant}', [PakasirWebhookController::class, 'tenant'])
    ->name('webhooks.pakasir.tenant');
Route::post('/webhooks/pakasir/platform', [PakasirWebhookController::class, 'platform'])
    ->name('webhooks.pakasir.platform');

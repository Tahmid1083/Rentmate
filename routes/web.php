<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TenantLeaseController;
use App\Http\Controllers\RentInvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;

// --------------------------------------------------------------------------
// Guest routes — login, registration, Google OAuth
// --------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'))->name('home');

    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Google OAuth (Sign in with Google / Gmail)
    Route::get('/auth/google/redirect',  [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback',  [GoogleAuthController::class, 'callback'])->name('google.callback');
});

// --------------------------------------------------------------------------
// Logout (works for any authenticated user)
// --------------------------------------------------------------------------
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// --------------------------------------------------------------------------
// Authenticated landlord area
// --------------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboard — operational summary
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Properties (a landlord owns many properties, each with many units)
    Route::resource('properties', PropertyController::class);

    // Units (nested under a property in the UI but addressable directly)
    Route::resource('properties.units', UnitController::class)->shallow();

    // Tenant leases (attach a tenant to a unit, store lease + rent + deposit)
    Route::resource('leases', TenantLeaseController::class);

    // Rent invoicing
    Route::resource('rent-invoices', RentInvoiceController::class);
    Route::post('rent-invoices/{rentInvoice}/payments',
        [PaymentController::class, 'store'])->name('rent-invoices.payments.store');

    // Payments
    Route::resource('payments', PaymentController::class)->only(['index', 'show', 'destroy']);
    Route::get('payments-export/csv', [PaymentController::class, 'exportCsv'])->name('payments.export.csv');

    // Maintenance requests with photo uploads
    Route::resource('maintenance', MaintenanceRequestController::class);
    Route::patch('maintenance/{maintenanceRequest}/status',
        [MaintenanceRequestController::class, 'updateStatus'])->name('maintenance.update-status');

    // Private documents (uploaded and served only to the landlord)
    Route::resource('documents', DocumentController::class);
    Route::get('documents/{document}/download',
        [DocumentController::class, 'download'])->name('documents.download');

    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::get('expenses-export/csv', [ExpenseController::class, 'exportCsv'])->name('expenses.export.csv');

    // Reports & analytics
    Route::get('reports',           [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/property',  [ReportController::class, 'propertyBreakdown'])->name('reports.property');

    // Settings
    Route::get('settings',    [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings',    [SettingsController::class, 'update'])->name('settings.update');
});

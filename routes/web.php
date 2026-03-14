<?php

use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\Admin\ContentTypeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EntryController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Setup\SetupController;
use Illuminate\Support\Facades\Route;

// ─── Root redirect ────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (file_exists(storage_path('app/installed.lock'))) {
        return redirect('/admin');
    }
    return redirect('/setup');
});

// ─── Setup Wizard (blocked after install) ─────────────────────────────────────
Route::middleware('not.installed')->prefix('setup')->name('setup.')->group(function () {
    Route::get('/',               [SetupController::class, 'welcome'])->name('welcome');
    Route::get('/database',       [SetupController::class, 'database'])->name('database');
    Route::post('/database/test', [SetupController::class, 'testDatabase'])->name('database.test');
    Route::post('/database',      [SetupController::class, 'saveDatabase'])->name('database.save');
    Route::get('/account',        [SetupController::class, 'account'])->name('account');
    Route::post('/account',       [SetupController::class, 'saveAccount'])->name('account.save');
    Route::get('/site-settings',  [SetupController::class, 'siteSettings'])->name('site-settings');
    Route::post('/site-settings', [SetupController::class, 'saveSiteSettings'])->name('site-settings.save');
    Route::get('/complete',       [SetupController::class, 'complete'])->name('complete');
});

// ─── Authentication ───────────────────────────────────────────────────────────
Route::middleware(['installed', 'guest'])->group(function () {
    Route::get('login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// ─── Admin Panel ──────────────────────────────────────────────────────────────
Route::middleware(['installed', 'auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Content Type Builder ──────────────────────────────────────────────────
    Route::prefix('content-type-builder')->name('ctb.')->group(function () {
        Route::get('/',                       [ContentTypeController::class, 'index'])->name('index');
        Route::get('/create/{kind}',          [ContentTypeController::class, 'create'])->name('create');
        Route::post('/',                      [ContentTypeController::class, 'store'])->name('store');
        Route::get('/{id}/edit',              [ContentTypeController::class, 'edit'])->name('edit');
        Route::put('/{id}',                   [ContentTypeController::class, 'update'])->name('update');
        Route::delete('/{id}',                [ContentTypeController::class, 'destroy'])->name('destroy');
        // Field management (JSON)
        Route::post('/{id}/fields',           [ContentTypeController::class, 'addField'])->name('fields.store');
        Route::put('/{id}/fields/{fid}',      [ContentTypeController::class, 'updateField'])->name('fields.update');
        Route::delete('/{id}/fields/{fid}',   [ContentTypeController::class, 'deleteField'])->name('fields.destroy');
        Route::post('/{id}/fields/reorder',   [ContentTypeController::class, 'reorderFields'])->name('fields.reorder');
    });

    // ── Content Manager ───────────────────────────────────────────────────────
    Route::prefix('content-manager')->name('cm.')->group(function () {
        Route::get('/{slug}',           [EntryController::class, 'index'])->name('index');
        Route::get('/{slug}/create',    [EntryController::class, 'create'])->name('create');
        Route::post('/{slug}',          [EntryController::class, 'store'])->name('store');
        Route::get('/{slug}/{id}/edit', [EntryController::class, 'edit'])->name('edit');
        Route::put('/{slug}/{id}',      [EntryController::class, 'update'])->name('update');
        Route::delete('/{slug}/{id}',   [EntryController::class, 'destroy'])->name('destroy');
    });

    // ── Media Library ─────────────────────────────────────────────────────────
    Route::prefix('media-library')->name('media.')->group(function () {
        Route::get('/',        [MediaController::class, 'index'])->name('index');
        Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
        Route::put('/{id}',    [MediaController::class, 'update'])->name('update');
        Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
    });

    // ── Settings — Global ─────────────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/global',  [SettingsController::class, 'global'])->name('global');
        Route::put('/global',  [SettingsController::class, 'updateGlobal'])->name('global.update');
    });

    // ── API Tokens ────────────────────────────────────────────────────────────
    Route::prefix('settings/api-tokens')->name('tokens.')->group(function () {
        Route::get('/',        [ApiTokenController::class, 'index'])->name('index');
        Route::get('/create',  [ApiTokenController::class, 'create'])->name('create');
        Route::post('/',       [ApiTokenController::class, 'store'])->name('store');
        Route::delete('/{id}', [ApiTokenController::class, 'destroy'])->name('destroy');
    });

    // ── Users ─────────────────────────────────────────────────────────────────
    Route::prefix('settings/users')->name('users')->group(function () {
        Route::get('/',          [SettingsController::class, 'users'])->name('');
        Route::get('/create',    [SettingsController::class, 'createUser'])->name('.create');
        Route::post('/',         [SettingsController::class, 'storeUser'])->name('.store');
        Route::get('/{id}/edit', [SettingsController::class, 'editUser'])->name('.edit');
        Route::put('/{id}',      [SettingsController::class, 'updateUser'])->name('.update');
        Route::delete('/{id}',   [SettingsController::class, 'destroyUser'])->name('.destroy');
    });
});

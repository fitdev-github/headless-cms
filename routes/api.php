<?php

use App\Http\Controllers\Api\ContentApiController;
use App\Http\Controllers\Api\UploadApiController;
use Illuminate\Support\Facades\Route;

// OPTIONS preflight for all API routes (CORS)
Route::options('{any}', function () {
    return response('', 200);
})->where('any', '.*');

// All API routes require installation + valid Bearer token + CORS headers
Route::middleware(['installed', 'api.token', 'cors'])->prefix('v1')->group(function () {

    // ── Upload / Media ────────────────────────────────────────────────────────
    // Specific routes must be declared BEFORE the dynamic {slug} wildcard
    Route::post('upload',              [UploadApiController::class, 'upload']);
    Route::get('upload/files',         [UploadApiController::class, 'files']);
    Route::get('upload/files/{id}',    [UploadApiController::class, 'fileById']);
    Route::delete('upload/files/{id}', [UploadApiController::class, 'destroy']);

    // ── Dynamic content endpoints (by plural slug) ────────────────────────────
    Route::get('{slug}',         [ContentApiController::class, 'index']);
    Route::post('{slug}',        [ContentApiController::class, 'create']);
    Route::get('{slug}/{id}',    [ContentApiController::class, 'find']);
    Route::put('{slug}/{id}',    [ContentApiController::class, 'update']);
    Route::delete('{slug}/{id}', [ContentApiController::class, 'delete']);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\PublicationApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\ExportApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});


Route::prefix('v1')->group(function () {
    // Token login (public)
    Route::post('/tokens', [AuthTokenController::class, 'store']);

    // Protected API
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/tokens', [AuthTokenController::class, 'destroy']);

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('users', UserApiController::class)->only(['index', 'show']);
        Route::apiResource('projects', ProjectApiController::class)->only(['index', 'show']);
        Route::post('/projects', [ProjectApiController::class, 'store'])->middleware('role:pi,manager');
        Route::match(['put', 'patch'], '/projects/{project}', [ProjectApiController::class, 'update'])->middleware('role:pi,manager');
        Route::delete('/projects/{project}', [ProjectApiController::class, 'destroy'])->middleware('role:pi,manager');

        Route::apiResource('publications', PublicationApiController::class)->only(['index', 'show']);
        Route::post('/publications', [PublicationApiController::class, 'store'])->middleware('role:pi,manager,researcher');
        Route::match(['put', 'patch'], '/publications/{publication}', [PublicationApiController::class, 'update'])->middleware('role:pi,manager,researcher');
        Route::delete('/publications/{publication}', [PublicationApiController::class, 'destroy'])->middleware('role:pi,manager,researcher');

        // export sincrono (GET)
        Route::get('/export/projects', [ExportApiController::class, 'projects'])
            ->middleware('ability:export:projects');

        Route::get('/export/publications', [ExportApiController::class, 'publications'])
            ->middleware('ability:export:publications');

        Route::get('/export/users', [ExportApiController::class, 'users'])
            ->middleware('ability:export:users', 'role:pi,manager');

        // export asincrono (POST + GET)
        Route::post('/exports', [ExportApiController::class, 'create'])
            ->middleware('throttle:export');

        Route::get('/exports/{export}', [ExportApiController::class, 'status'])
            ->middleware('can:view,export');

        Route::get('/exports/{export}/download', [ExportApiController::class, 'download'])
            ->middleware('can:download,export');
    });
});
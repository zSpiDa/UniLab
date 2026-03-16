<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\PublicationApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\MilestoneApiController;
use App\Http\Controllers\Api\CommentApiController;
use App\Http\Controllers\Api\TagApiController;
use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\Api\AttachmentApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\AuthTokenController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
Route::post('v1/tokens', [AuthTokenController::class, 'store']);
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::delete('/tokens', [AuthTokenController::class, 'destroy']);
    Route::apiResource('projects', ProjectApiController::class);
    Route::apiResource('publications', PublicationApiController::class);

    Route::get('/user', function (Request $r) {
        return $r->user();
    });
});

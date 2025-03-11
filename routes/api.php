<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Broadcast::routes();

        Route::get('/user', function (Request $request) {
            return response()->json($request->user());
        });

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::delete('tasks/bulk', [TaskController::class, 'destroyBulk'])->name('tasks.bulk-delete');
        Route::apiResource('tasks', TaskController::class);

        // Route to get Authorization Bearer token
        Route::get('/auth/token', function (Request $request) {
            $token = $request->user()->createToken(name: 'api-token')->plainTextToken;
            return response()->json(['token' => $token]);
        });
    });
});

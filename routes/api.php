<?php

use App\Http\Controllers\SensorController;
use App\Http\Controllers\SensorHeartbeatController;
use App\Http\Controllers\SensorRuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('sensors')->group(function () {
    Route::post('/login', [SensorController::class, 'login']);
    Route::post('/register', [SensorController::class, 'register']);
    Route::post('/heartbeat', [SensorHeartbeatController::class, 'heartbeat']);
    Route::patch('/update/{id}', [SensorController::class, 'edit']);
    Route::get('/uuid', [SensorController::class, 'get']);
    Route::delete('delete/{id}', [SensorController::class, 'delete']);
    Route::get('/{id}', [SensorController::class, 'detail']);
    Route::patch('/update_status/{id}', [SensorController::class, 'updateStatus']);
    Route::post('/update_rule/{id}', [SensorRuleController::class, 'upload']);
});


Route::get('test/{id}',[SensorController::class, 'getSensorCounts']);
Route::get('log/{id}',[SensorHeartbeatController::class, 'getLog']);

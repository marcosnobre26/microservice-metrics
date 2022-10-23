<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MetricCourseController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(array('prefix' => 'courses'), function () {
    Route::get('/', [MetricCourseController::class, 'index']);
    Route::post('/create', [MetricCourseController::class, 'create']);
    Route::get('/{id}', [MetricCourseController::class, 'show']);
    Route::post('/update', [MetricCourseController::class, 'update']);
    Route::delete('/{id}', [MetricCourseController::class, 'delete']);
});

Route::group(array('prefix' => 'classes'), function () {
    Route::get('/', [MetricCourseController::class, 'index']);
    Route::post('/create', [MetricCourseController::class, 'create']);
    Route::get('/{id}', [MetricCourseController::class, 'show']);
    Route::post('/update', [MetricCourseController::class, 'update']);
    Route::delete('/{id}', [MetricCourseController::class, 'delete']);
});
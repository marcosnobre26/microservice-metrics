<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MetricClassesController;
use App\Http\Controllers\API\MetricCourseController;
use App\Http\Controllers\API\MetricUsersController;
use App\Http\Controllers\API\MetricModulesController;

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
    Route::get('/search/{id}', [MetricCourseController::class, 'index']);
    Route::post('/create', [MetricCourseController::class, 'create']);
});

Route::group(array('prefix' => 'classes'), function () {
    Route::get('/', [MetricClassesController::class, 'index']);
    Route::post('/create', [MetricClassesController::class, 'create']);
});

Route::group(array('prefix' => 'modules'), function () {
    Route::get('/', [MetricModulesController::class, 'index']);
    Route::post('/create', [MetricModulesController::class, 'create']);
});

Route::group(array('prefix' => 'users'), function () {
    Route::get('/', [MetricUsersController::class, 'index']);
    Route::post('/create', [MetricUsersController::class, 'create']);
});
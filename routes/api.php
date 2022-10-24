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

Route::group(array('prefix' => 'metrics'), function () {
    Route::group(array('prefix' => 'courses'), function () {
        Route::get('/{order}/{perPage}', [MetricCourseController::class, 'index']);
        Route::get('/searchCursos/{search}/{order}', [MetricCourseController::class, 'searchCursos']);
        Route::post('/update/{id}', [MetricCourseController::class, 'update']);
        Route::post('/create/{id}', [MetricCourseController::class, 'create']);
        Route::get('/search-time-consumed/{order}/{course_id}/{perPage}', [MetricCourseController::class, 'searchTimeConsumed']);
        Route::get('/search-users-finished/{order}', [MetricCourseController::class, 'searchUsersFinished']);
        Route::get('/plan-course/{plan}/{order}/{perPage}', [MetricCourseController::class, 'planCourses']);
        Route::get('/plans', [MetricCourseController::class, 'plans']);
    });

    Route::group(array('prefix' => 'classes'), function () {
        Route::get('/', [MetricClassesController::class, 'index']);
        Route::post('/update/{id}', [MetricCourseController::class, 'update']);
        Route::post('/create/{id}', [MetricCourseController::class, 'create']);
    });

    Route::group(array('prefix' => 'modules'), function () {
        Route::get('/', [MetricModulesController::class, 'index']);
        Route::post('/create/{id}', [MetricModulesController::class, 'create']);
        Route::post('/update/{id}', [MetricModulesController::class, 'update']);
    });

    Route::group(array('prefix' => 'users'), function () {
        Route::get('/', [MetricUsersController::class, 'index']);
        Route::post('/create/{id}', [MetricUsersController::class, 'create']);
        Route::post('/update/{id}', [MetricUsersController::class, 'update']);
    });
});
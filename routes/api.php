<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MetricClassesController;
use App\Http\Controllers\API\MetricCourseController;
use App\Http\Controllers\API\MetricUsersController;
use App\Http\Controllers\API\MetricModulesController;
use App\Http\Controllers\API\ExportController;

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

Route::group(array('prefix' => 'user'), function () {
    Route::get('/tenants/{id}', [MetricCourseController::class, 'tenants']);
});

Route::group(array('prefix' => 'export-csv'), function () {
    Route::get('/course/{id}/{tenant_id}', [ExportController::class, 'exportCourse']);
});

Route::group(array('prefix' => 'metrics'), function () {
    Route::group(array('prefix' => 'courses'), function () {
        Route::get('/', [MetricCourseController::class, 'index']);
        Route::get('/searchCursos/{search}/{order}', [MetricCourseController::class, 'searchCursos']);
        Route::post('/update/{id}', [MetricCourseController::class, 'update']);
        Route::post('/create/{id}', [MetricCourseController::class, 'create']);
        Route::get('/search-name/{search}/{package_id}/{perPage}/{order}', [MetricCourseController::class, 'searchName']);
        Route::get('/search-time-consumed/{order}/{course_id}/{perPage}', [MetricCourseController::class, 'searchTimeConsumed']);
        Route::get('/search-name-students/{search}/{id_course}/{perPage}', [MetricCourseController::class, 'studentsFilterName']);
        Route::get('/search-document-students/{search}/{id_course}/{perPage}', [MetricCourseController::class, 'studentsFilterCPF']);
        Route::get('/search-email-students/{search}/{id_course}/{perPage}', [MetricCourseController::class, 'studentsFilterEmail']);
        Route::get('/search-users-finished/{order}', [MetricCourseController::class, 'searchUsersFinished']);
        Route::get('/plan-course/{plan}/{order}/{perPage}', [MetricCourseController::class, 'planCourses']);
        Route::get('/students-to-courses/{id_course}/{perPage}', [MetricCourseController::class, 'studentsToCourses']);
        Route::get('/plans', [MetricCourseController::class, 'plans']);
        Route::post('/show/{id}', [MetricCourseController::class, 'update']);
    });

    Route::group(array('prefix' => 'classes'), function () {
        Route::get('/', [MetricClassesController::class, 'index']);
        Route::post('/update/{id}', [MetricClassesController::class, 'update']);
        Route::post('/create/{id}', [MetricClassesController::class, 'create']);
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
        Route::get('/courses/{id}', [MetricUsersController::class, 'getCoursesUser']);
    });
});
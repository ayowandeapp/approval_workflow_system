<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ApprovalFlowController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ApprovalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    // User routes
    Route::get('/users', [AuthController::class, 'index']);
    Route::get('/users/{user}', [AuthController::class, 'show']);
    Route::patch('/users/{user}', [AuthController::class, 'update']);
    Route::delete('/users/{user}', [AuthController::class, 'destroy']);

    // Department routes
    Route::apiResource('departments', DepartmentController::class);

    // Approval Flow routes
    Route::get('/approval-flows', [ApprovalFlowController::class, 'index']);
    Route::post('/approval-flows', [ApprovalFlowController::class, 'store']);
    Route::patch('/approval-flows/{approvalFlow}', [ApprovalFlowController::class, 'update']);
    Route::delete('/approval-flows/{approvalFlow}', [ApprovalFlowController::class, 'destroy']);

    // Request routes
    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{request}', [RequestController::class, 'show']);
    Route::get('/requests/{request}/steps', [RequestController::class, 'steps']);

    // Approval routes
    Route::post('/requests/{requestModel}/approve', [ApprovalController::class, 'approve'])
        ->middleware('approver');
    Route::post('/requests/{requestModel}/reject', [ApprovalController::class, 'reject'])
        ->middleware('approver');
});
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ScoutController;
use App\Http\Controllers\AdultLeaderController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\DashboardController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Family Routes
Route::apiResource('families', FamilyController::class);
Route::post('/families/merge', [FamilyController::class, 'merge']);

// Person Routes
Route::apiResource('persons', PersonController::class);
Route::get('/persons/orphaned/search', [PersonController::class, 'searchOrphaned']);
Route::post('/persons/merge', [PersonController::class, 'merge']);

// Scout Routes
Route::apiResource('scouts', ScoutController::class);
Route::get('/scouts/expiring/list', [ScoutController::class, 'expiringScouts']);
Route::get('/scouts/den/{den}', [ScoutController::class, 'byDen']);

// Adult Leader Routes
Route::apiResource('leaders', AdultLeaderController::class);
Route::get('/leaders/expiring/soon', [AdultLeaderController::class, 'expiringSoon']);
Route::post('/leaders/{leader}/positions', [AdultLeaderController::class, 'addPosition']);
Route::delete('/leaders/{leader}/positions', [AdultLeaderController::class, 'removePosition']);

// User Permission Routes
Route::apiResource('permissions', UserPermissionController::class);
Route::get('/permissions/role/{role}', [UserPermissionController::class, 'byRole']);
Route::get('/permissions/wordpress/{wordpress_user_id}', [UserPermissionController::class, 'byWordPressUser']);
Route::get('/permissions/admins/list', [UserPermissionController::class, 'admins']);

// Dashboard Routes
Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
Route::get('/dashboard/activity', [DashboardController::class, 'recentActivity']);
Route::get('/dashboard/expiring', [DashboardController::class, 'expiringRecords']);
Route::get('/dashboard/orphaned', [DashboardController::class, 'orphanedPersons']);
Route::get('/dashboard/sync-status', [DashboardController::class, 'syncStatus']);
Route::get('/dashboard/sync-history', [DashboardController::class, 'syncHistory']);
Route::get('/dashboard/family/{family_id}', [DashboardController::class, 'familyMembers']);
Route::get('/dashboard/dens', [DashboardController::class, 'denMembership']);
Route::get('/dashboard/ranks', [DashboardController::class, 'rankDistribution']);

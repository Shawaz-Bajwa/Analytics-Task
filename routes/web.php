<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/analytics/store', [DashboardController::class, 'store'])->name('analytics.store');
Route::get('/analytics/edit/{id}', [DashboardController::class, 'edit'])->name('analytics.edit');
Route::put('/analytics/update/{id}', [DashboardController::class, 'update'])->name('analytics.update');
Route::delete('/analytics/delete/{id}', [DashboardController::class, 'destroy'])->name('analytics.delete');

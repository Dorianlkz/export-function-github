<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;

Route::get('/', [ExportController::class, 'showForm']);

// Export route
Route::get('/export', [ExportController::class, 'exportForm'])->name('export.form');

<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');



// Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');

    Route::resource('product', \App\Http\Controllers\ProductController::class);
    Route::resource('invoice', \App\Http\Controllers\InvoiceController::class);
    Route::get('/invoice/{id}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoice.print');

    Route::resource('group-user', \App\Http\Controllers\GroupUserController::class);
    Route::resource('menu', \App\Http\Controllers\MenuController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';

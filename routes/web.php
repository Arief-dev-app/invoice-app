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



Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
    Route::post('/company', [CompanyController::class, 'store'])->name('company.store');


    Route::resource('invoice', \App\Http\Controllers\InvoiceController::class);
    Route::get('/invoice/{id}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoice.print');

    Route::resource('group-user', \App\Http\Controllers\GroupUserController::class);
    Route::post('group-user/{id}/restore', [\App\Http\Controllers\GroupUserController::class, 'restore'])->name('group-user.restore');

    Route::resource('menu', \App\Http\Controllers\MenuController::class);
    
    Route::resource('role-user', \App\Http\Controllers\RoleUserController::class);
    Route::get('/role-user/{id}/menus', [\App\Http\Controllers\RoleUserController::class, 'getMenusByGroup']);
    
    Route::resource('user', \App\Http\Controllers\UserController::class);

     // Master
     Route::resource('product', \App\Http\Controllers\ProductController::class);
     Route::post('product/{id}', [\App\Http\Controllers\ProductController::class, 'destroy']);

     Route::resource('customer', \App\Http\Controllers\CustomerController::class);
     Route::post('customer/cekAkses', [\App\Http\Controllers\CustomerController::class, 'cekAkses']);
     
     Route::resource('supplier', \App\Http\Controllers\SupplierController::class);
     
     //  Transaksi
     Route::resource('purchase-order', \App\Http\Controllers\PurchaseOrderController::class);
     Route::post('purchase-order/cekAkses', [\App\Http\Controllers\PurchaseOrderController::class, 'cekAkses']);
     Route::put('/purchase-order/{id}/confirm', [\App\Http\Controllers\PurchaseOrderController::class, 'confirm']);
     
     Route::resource('transaksi-pembelian', \App\Http\Controllers\PurchaseController::class);
     Route::post('transaksi-pembelian/cekAkses', [\App\Http\Controllers\PurchaseController::class, 'cekAkses']);
     Route::get('/transaksi-pembelian/{id}/SearchPo', [\App\Http\Controllers\PurchaseController::class, 'searchPo']);
     Route::put('/transaksi-pembelian/{id}/confirm', [\App\Http\Controllers\PurchaseController::class, 'confirm']);
     
     Route::resource('retur-pembelian', \App\Http\Controllers\ReturPurchaseController::class);
     Route::post('retur-pembelian/cekAkses', [\App\Http\Controllers\ReturPurchaseController::class, 'cekAkses']);
     Route::get('/retur-pembelian/{id}/SearchPo', [\App\Http\Controllers\ReturPurchaseController::class, 'searchPo']);
     Route::put('/retur-pembelian/{id}/confirm', [\App\Http\Controllers\ReturPurchaseController::class, 'confirm']);


    

    

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

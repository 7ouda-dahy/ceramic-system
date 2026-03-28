<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\CashboxController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\WasteController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/password', [UserController::class, 'editPassword'])->name('users.password.edit');
    Route::post('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password.update');

    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/stock/add', [StockController::class, 'create'])->name('stock.create');
    Route::post('/stock/add', [StockController::class, 'store'])->name('stock.store');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');

    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

    Route::get('/purchase-invoices', [PurchaseInvoiceController::class, 'index'])->name('purchase-invoices.index');
    Route::get('/purchase-invoices/create', [PurchaseInvoiceController::class, 'create'])->name('purchase-invoices.create');
    Route::post('/purchase-invoices', [PurchaseInvoiceController::class, 'store'])->name('purchase-invoices.store');
    Route::get('/purchase-invoices/{id}', [PurchaseInvoiceController::class, 'show'])->name('purchase-invoices.show');
    Route::get('/purchase-invoices/{id}/print', [PurchaseInvoiceController::class, 'print'])->name('purchase-invoices.print');

    Route::get('/sales-returns', [SalesReturnController::class, 'index'])->name('sales-returns.index');
    Route::get('/sales-returns/create', [SalesReturnController::class, 'create'])->name('sales-returns.create');
    Route::post('/sales-returns', [SalesReturnController::class, 'store'])->name('sales-returns.store');
    Route::get('/sales-returns/{id}', [SalesReturnController::class, 'show'])->name('sales-returns.show');
    Route::get('/sales-returns/{id}/print', [SalesReturnController::class, 'print'])->name('sales-returns.print');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');

    Route::get('/customer-payments/create', [CustomerPaymentController::class, 'create'])->name('customer-payments.create');
    Route::post('/customer-payments', [CustomerPaymentController::class, 'store'])->name('customer-payments.store');
    Route::get('/customer-payments/{id}', [CustomerPaymentController::class, 'show'])->name('customer-payments.show');
    Route::get('/customer-payments/{id}/print', [CustomerPaymentController::class, 'print'])->name('customer-payments.print');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->name('suppliers.show');

    Route::get('/supplier-payments/create', [SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
    Route::post('/supplier-payments', [SupplierPaymentController::class, 'store'])->name('supplier-payments.store');
    Route::get('/supplier-payments/{id}', [SupplierPaymentController::class, 'show'])->name('supplier-payments.show');
    Route::get('/supplier-payments/{id}/print', [SupplierPaymentController::class, 'print'])->name('supplier-payments.print');

    Route::get('/cashbox/{slug}', [CashboxController::class, 'index'])->name('cashbox.show');
    Route::post('/cashbox/{slug}/expense', [CashboxController::class, 'storeExpense'])->name('cashbox.expense');
    Route::post('/cashbox/{slug}/transfer', [CashboxController::class, 'storeTransfer'])->name('cashbox.transfer');
    Route::get('/cashbox/{slug}/print', [CashboxController::class, 'print'])->name('cashbox.print');

    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');
    Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->name('backups.download');
    Route::post('/backups/restore/{filename}', [BackupController::class, 'restore'])->name('backups.restore');

    Route::get('/wastes', [WasteController::class, 'index'])->name('wastes.index');
Route::get('/wastes/create', [WasteController::class, 'create'])->name('wastes.create');
Route::post('/wastes', [WasteController::class, 'store'])->name('wastes.store');
Route::get('/wastes/{id}', [WasteController::class, 'show'])->name('wastes.show');

});
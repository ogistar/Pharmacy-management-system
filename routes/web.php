<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PrescriptionController;
use App\Http\Controllers\Admin\ReceivableController;
use App\Http\Controllers\Admin\SaleReturnController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\CashSessionController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\StockOpnameController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\CompoundController;
use App\Http\Controllers\Admin\PosReportController;
use Illuminate\Support\Facades\App;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth'])->group(function(){
    Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
    Route::get('',[DashboardController::class,'Index']);
    Route::get('notification',[NotificationController::class,'markAsRead'])->name('mark-as-read');
    Route::get('notification-read',[NotificationController::class,'read'])->name('read');
    Route::get('notification-expiry',[NotificationController::class,'refreshExpiry'])->name('notification.expiry');
    Route::get('profile',[UserController::class,'profile'])->name('profile');
    Route::post('profile/{user}',[UserController::class,'updateProfile'])->name('profile.update');
    Route::put('profile/update-password/{user}',[UserController::class,'updatePassword'])->name('update-password');
    Route::post('logout',[LogoutController::class,'index'])->name('logout');

    Route::resource('users',UserController::class);
    Route::resource('permissions',PermissionController::class)->only(['index','store','destroy']);
    Route::put('permission',[PermissionController::class,'update'])->name('permissions.update');
    Route::resource('roles',RoleController::class);
    Route::resource('suppliers',SupplierController::class);
    Route::resource('categories',CategoryController::class)->only(['index','store','destroy']);
    Route::put('categories',[CategoryController::class,'update'])->name('categories.update');
    Route::resource('purchases',PurchaseController::class)->except('show');
    Route::get('purchases/reports',[PurchaseController::class,'reports'])->name('purchases.report');
    Route::post('purchases/reports',[PurchaseController::class,'generateReport']);
    Route::resource('products',ProductController::class)->except('show');
    Route::get('products/outstock',[ProductController::class,'outstock'])->name('outstock');
    Route::get('products/expired',[ProductController::class,'expired'])->name('expired');
    Route::get('batches',[BatchController::class,'index'])->name('batches.index');
    Route::get('stock-opnames',[StockOpnameController::class,'index'])->name('stock-opnames.index');
    Route::post('stock-opnames',[StockOpnameController::class,'store'])->name('stock-opnames.store');
    Route::post('stock-transfers',[StockTransferController::class,'store'])->name('stock-transfers.store');
    Route::resource('compounds', CompoundController::class)->except(['create','edit','show']);
    // Sales module disabled; reports handled via POS invoices
    // Route::resource('sales',SaleController::class)->except('show');
    Route::get('sales/reports',[PosReportController::class,'index'])->name('sales.report');
    Route::post('sales/reports',[PosReportController::class,'generateReport']);
    Route::get('sales/search',[PosReportController::class,'search'])->name('sales.search');
    Route::get('sales/{invoice}/items',[PosReportController::class,'items'])->name('sales.items');
    Route::get('sale-items/search', [SaleReturnController::class,'search'])->name('sale-items.search');
    Route::get('sale-items/{saleItem}/preview', [SaleReturnController::class,'preview'])->name('sale-items.preview');
    Route::get('purchases/{purchase}/preview', [PurchaseController::class,'preview'])->name('purchases.preview');
    Route::get('purchases/search',[PurchaseController::class,'search'])->name('purchases.search');

    // POS (Kasir)
    Route::get('pos',[PosController::class,'index'])->name('pos.index');
    Route::post('pos/checkout',[PosController::class,'checkout'])->name('pos.checkout');
    Route::get('pos/products',[PosController::class,'searchProducts'])->name('pos.products');
    Route::get('pos/from-prescription/{prescription}',[PosController::class,'fromPrescription'])->name('pos.from-prescription');
    Route::get('pos/compounds',[PosController::class,'compounds'])->name('pos.compounds');

    // Patients autocomplete (used by POS LOV)
    Route::get('patients/search', [PatientController::class, 'search'])->name('patients.search');

    Route::get('backup', [BackupController::class,'index'])->name('backup.index');
    Route::put('backup/create', [BackupController::class,'create'])->name('backup.store');
    Route::get('backup/download/{file_name?}', [BackupController::class,'download'])->name('backup.download');
    Route::delete('backup/delete/{file_name?}', [BackupController::class,'destroy'])->where('file_name', '(.*)')->name('backup.destroy');

    Route::get('settings',[SettingController::class,'index'])->name('settings');

    // Patients & prescriptions
    Route::resource('patients', PatientController::class)->except(['create','edit','show']);
    Route::resource('prescriptions', PrescriptionController::class)->except(['create','edit','show']);
    Route::post('prescriptions/{prescription}/approve',[PrescriptionController::class,'approve'])->name('prescriptions.approve');
    Route::post('prescriptions/{prescription}/dispense',[PrescriptionController::class,'dispense'])->name('prescriptions.dispense');

    // Piutang / receivables
    Route::resource('receivables', ReceivableController::class)->only(['index','show']);
    Route::post('receivables/{receivable}/pay',[ReceivableController::class,'pay'])->name('receivables.pay');

    // Returns & stock adjustments
    Route::post('sale-returns',[SaleReturnController::class,'store'])->name('sale-returns.store');
    Route::post('purchase-returns',[PurchaseReturnController::class,'store'])->name('purchase-returns.store');
    Route::post('stock-adjustments',[StockAdjustmentController::class,'store'])->name('stock-adjustments.store');
    Route::view('stock-tools','admin.stock-tools.index')->name('stock-tools.index');
    Route::get('stock-movements', [\App\Http\Controllers\Admin\StockMovementController::class,'index'])->name('stock-movements.index');
    Route::get('sales/{invoice}', [PosReportController::class,'show'])->name('sales.show');
    Route::get('purchases/{purchase}', [PurchaseController::class,'preview'])->name('purchases.preview');
    Route::get('sale-items/search', [SaleReturnController::class,'search'])->name('sale-items.search');

    // Cashier sessions
    Route::get('cash-sessions',[CashSessionController::class,'index'])->name('cash-sessions.index');
    Route::post('cash-sessions/open',[CashSessionController::class,'open'])->name('cash-sessions.open');
    Route::post('cash-sessions/{cashSession}/close',[CashSessionController::class,'close'])->name('cash-sessions.close');
});

Route::middleware(['guest'])->group(function () {
    Route::get('',function(){
        return redirect()->route('dashboard');
    });

    Route::get('login',[LoginController::class,'index'])->name('login');
    Route::post('login',[LoginController::class,'login']);

    Route::get('register',[RegisterController::class,'index'])->name('register');
    Route::post('register',[RegisterController::class,'store']);

    Route::get('forgot-password',[ForgotPasswordController::class,'index'])->name('password.request');
    Route::post('forgot-password',[ForgotPasswordController::class,'requestEmail']);
    Route::get('reset-password/{token}',[ResetPasswordController::class,'index'])->name('password.reset');
    Route::post('reset-password',[ResetPasswordController::class,'resetPassword'])->name('password.update');
});

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session(['locale' => $locale]);
        App::setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');

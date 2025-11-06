<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\FundTransferController;
use App\Http\Controllers\Admin\ChequeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TransactionsController;
use App\Http\Controllers\Admin\ReturnChequeController;
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StocksController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\ReceivingPaymentsController;
use App\Http\Controllers\Admin\ReceivingChequeController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\BomComponentController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\ManufactureController;
use App\Http\Controllers\Admin\BomStockController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\InvoicesController;
use App\Http\Controllers\Admin\UserLogController;
use App\Http\Controllers\Admin\BankAccountReportController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\PurchaseReportController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\BomStockAdjustmentController;
use App\Http\Controllers\Admin\SaleReturnController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductBrandController;
use App\Http\Controllers\Admin\CustomerPaymentsController;
use App\Http\Controllers\Admin\SupplierPaymentsController;
use App\Http\Controllers\Admin\LoanController as MainLoanController;
use App\Http\Controllers\Admin\Accounts\LoanController as AccountLoanController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Admin\PurchaseManufacturedProductController;
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
// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});
Route::get('/clear-route', function () {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('optimize:clear'); // optional
    return "Route cache cleared!";
});
Route::get('/run-migrations', function () {
    try {
        // Run all pending migrations
        Artisan::call('migrate', [
            '--force' => true, // bypass confirmation in production
        ]);

        // Get output
        $output = Artisan::output();

        return "<h2>Migrations executed successfully!</h2><pre>{$output}</pre>";
    } catch (\Exception $e) {
        return "<h2>Error running migrations:</h2> <pre>{$e->getMessage()}</pre>";
    }
});
// Run UpdateMemberRoleSeeder
Route::get('/run-update-member-role-seeder', function () {
    try {
        Artisan::call('db:seed', [
            '--class' => 'UpdateMemberRoleSeeder',
            '--force' => true, // bypass confirmation in production
        ]);

        $output = Artisan::output();
        return "<h2>Seeder executed successfully!</h2><pre>{$output}</pre>";
    } catch (\Exception $e) {
        return "<h2>Error running seeder:</h2> <pre>{$e->getMessage()}</pre>";
    }
});
// Authentication Routes...
Auth::routes();

// Home Routes
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// User Routes
Route::middleware(['auth'])->get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');

// Admin Routes
Route::middleware(['auth'])->get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

// AJAX route to fetch branches based on selected bank
Route::middleware(['auth'])->get('/bank-accounts/branches', [BankAccountController::class, 'getBranches'])->name('admin.bank-accounts.branches');

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
Route::post('admin/cheques/paid/{cheque}', [ChequeController::class, 'markPaid']);
Route::post('admin/receiving-cheques/paid/{cheque}', [ReceivingChequeController::class, 'markReceived']);

//Table Routes
Route::middleware('auth')->group(function () {
    Route::post('admin/invoices/{sale}/customer-payment', [InvoicesController::class, 'addCustomerPayment'])->name('admin.invoices.addCustomerPayment');
    Route::post('admin/invoices/{sale}/sale-payment', [InvoicesController::class, 'addSalePayment'])->name('admin.invoices.addSalePayment');
    Route::get('/admin/accounts/table', [AccountManagementController::class, 'table'])->name('admin.accounts.table');
    Route::get('/admin/expenses/table', [ExpenseController::class, 'table'])->name('admin.expenses.table');
    Route::get('/admin/receiving-payments/table', [ReceivingPaymentsController::class, 'table'])->name('admin.receiving-payments.table');
    Route::get('/admin/receiving-cheques/table', [ReceivingChequeController::class, 'table'])->name('admin.receiving-cheques.table');
    Route::get('/admin/transactions/table', [TransactionsController::class, 'table'])->name('admin.transactions.table');
    Route::get('/admin/return-cheques/table', [ReturnChequeController::class, 'table'])->name('admin.return-cheques.table');
    Route::get('/admin/cheques/table', [ChequeController::class, 'table'])->name('admin.cheques.table');
    Route::get('/admin/fund-transfers/table', [FundTransferController::class, 'table'])->name('admin.fund-transfers.table');
    Route::get('/admin/bank-accounts/table', [BankAccountController::class, 'table'])->name('admin.bank-accounts.table');
    Route::get('/admin/suppliers/table', [SupplierController::class, 'table'])->name('admin.suppliers.table')->middleware('auth');
    Route::get('admin/customer/table', [CustomerController::class, 'table'])->name('admin.customers.table')->middleware('auth');
    Route::get('/admin/users/table', [AdminUserController::class, 'table'])->name('admin.users.table')->middleware('auth')->middleware('auth');
    Route::get('/admin/bom/table', [BomComponentController::class, 'table'])->name('admin.bom.table');
    Route::get('/admin/bom-stocks/table', [BOMStockController::class, 'table'])->name('admin.bom-stocks.table');
    Route::get('/admin/manufactures/table', [ManufactureController::class, 'table'])->name('admin.manufactures.table');
    Route::get('/admin/product-categories/table', [ProductCategoryController::class, 'table'])->name('admin.product-categories.table');
    Route::get('/admin/products/table', [ProductController::class, 'table'])->name('admin.products.table');
    Route::get('/admin/purchases/table', [PurchaseController::class, 'table'])->name('admin.purchases.table');
    Route::get('/admin/sales/table', [SalesController::class, 'table'])->name('admin.sales.table');
    Route::get('/admin/invoices/purchases-table', [InvoicesController::class, 'purchasestable'])->name('admin.invoices.purchases');
    Route::get('/admin/invoices/sales-table', [InvoicesController::class, 'salestable'])->name('admin.invoices.sales');
    Route::get('/admin/stocks/table', [StocksController::class, 'table'])->name('admin.stocks.table');
    Route::get('/admin/stock-adjustments/table', [StockAdjustmentController::class, 'table'])->name('admin.stock-adjustments.table');
    Route::get('/admin/bom-stock-adjustments/table', [BomStockAdjustmentController::class, 'table'])->name('admin.bom-stock-adjustments.table');
    Route::get('/admin/sales-return/table', [SaleReturnController::class, 'table'])->name('admin.sales-return.table');
    Route::get('/admin/purchase-returns/table', [PurchaseReturnController::class, 'table'])->name('admin.purchase-returns.table');
    Route::post('/admin/receiving-cheques/mark-received/{id}', [ReceivingChequeController::class, 'markReceived'])->name('admin.receiving-cheques.mark-received');
    Route::get('/admin/customers-payments/{customer}', [CustomerPaymentsController::class, 'fetchCustomerPayments']);
    Route::post('/admin/customers-payments/{customer}/store', [CustomerPaymentsController::class, 'store'])->name('admin.customers-payments.store');
    Route::get('/admin/suppliers-payments/{supplier}', [SupplierPaymentsController::class, 'fetchSupplierPurchases']);
    Route::post('/admin/suppliers-payments/{supplier}/store', [SupplierPaymentsController::class, 'store'])->name('admin.suppliers-payments.store');
    Route::get('/admin/products-by-category/{category}', [BomComponentController::class, 'getProductsByCategory'])->name('admin.products.byCategory'); 
    Route::put('/admin/customer-loans/{id}/status', [AccountLoanController::class, 'updateStatus'])->name('admin.customer-loans.updateStatus');
    Route::get('/admin/manufactures/product/{productId}/components', [ManufactureController::class, 'getProductComponents'])->name('admin.manufactures.get.components');
    Route::get('/admin/customers/ledger', [CustomerController::class, 'ledger'])->name('admin.customers.ledger');
    Route::get('/admin/customers/ledger/search', [CustomerController::class, 'ledgerSearch'])->name('admin.customers.ledger.search');

});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth:web'])->group(function () {
    Route::resource('purchase_manufactured_products', PurchaseManufacturedProductController::class);
    Route::resource('transfers', TransferController::class);
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('customer-loans', AccountLoanController::class);
    Route::resource('loans', MainLoanController::class);
    Route::resource('supplier-payments', SupplierPaymentsController::class);
    Route::resource('customers-payments', CustomerPaymentsController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('product-brand', ProductBrandController::class);
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/history', [CustomerController::class, 'history'])->name('customers.history');
    Route::get('purchase-report', [PurchaseReportController::class, 'index'])->name('purchase.report.index');
    Route::get('purchase-report/pdf', [PurchaseReportController::class, 'pdf'])->name('purchase.report.pdf');
    Route::get('product/{product}/bom-components', [PurchaseReportController::class, 'getBomComponents'])->name('product.bom-components');
    Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales.report.index');
    Route::get('sales-report/pdf', [SalesReportController::class, 'pdf'])->name('sales.report.pdf');
    Route::get('/account/report/pdf', [BankAccountReportController::class, 'exportPdf'])->name('account.report.pdf'); 
    Route::get('/account/report', [BankAccountReportController::class, 'index'])->name('account.report.index');
    Route::resource('users', AdminUserController::class);
    Route::resource('user-logs', UserLogController::class);
    Route::resource('product-categories', ProductCategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('stocks', StocksController::class);
    Route::resource('stock-adjustments', StockAdjustmentController::class);
    Route::resource('bom-stock-adjustments', BomStockAdjustmentController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/history', [SupplierController::class, 'history'])->name('suppliers.history');
    Route::resource('bom', BomComponentController::class);
    Route::resource('bom-stocks', BomStockController::class);
    Route::get('products/{product}/bom-components', [PurchaseController::class, 'getBomComponents'])->name('admin.products.bom-components');
    Route::resource('manufactures', ManufactureController::class);
    Route::get('manufactures/product/{productId}/components', [ManufactureController::class, 'getProductComponents'])->name('manufactures.components');
    Route::resource('sales', SalesController::class);
    Route::resource('sales-returns', SaleReturnController::class);
    Route::get('sales/{sale}/print', [SalesController::class, 'print'])->name('sales.print');
    Route::get('invoices/sales', [InvoicesController::class, 'index'])->name('invoices.sales');
    Route::get('invoices/purchases', [InvoicesController::class, 'purchases'])->name('invoices.purchases');
    Route::get('invoices/purchases/print/{purchase}', [InvoicesController::class, 'printPurchase'])->name('invoices.printPurchase');
    Route::resource('purchases', PurchaseController::class);
    Route::resource('purchase-returns', PurchaseReturnController::class);
    Route::get('accounts/menu', [AccountController::class, 'accounts'])->name('accounts.menu');
    Route::get('tog/menu', [AccountController::class, 'tog'])->name('tog.menu');
    Route::get('product/menu', [AccountController::class, 'products'])->name('products.menu');
    Route::get('user/menu', [AccountController::class, 'users'])->name('users.menu');
    Route::get('inventory/menu', [AccountController::class, 'inventory'])->name('inventory.menu');
    Route::get('purchase/menu', [AccountController::class, 'purchase'])->name('purchase.menu');
    Route::get('sale/menu', [AccountController::class, 'sales'])->name('sales.menu');
    Route::get('bom-section/menu', [AccountController::class, 'bom'])->name('bom.menu');
    Route::get('customer/menu', [AccountController::class, 'customer'])->name('customers.menu');
    Route::get('supplier/menu', [AccountController::class, 'supplier'])->name('suppliers.menu');
    Route::get('reports/menu', [AccountController::class, 'reports'])->name('reports.menu');
    Route::get('brand/menu', [AccountController::class, 'brands'])->name('brands.menu');
    Route::resource('accounts', AccountManagementController::class);
    Route::get('/accounts/{bankId}/transactions', [AccountManagementController::class, 'transactions'])->name('accounts.transactions');
    Route::resource('fund-transfers', FundTransferController::class);
    Route::resource('receiving-payments', ReceivingPaymentsController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('receiving-cheques', ReceivingChequeController::class);
    Route::resource('cheques', ChequeController::class);
    Route::resource('return-cheques', ReturnChequeController::class);
    Route::post('cheques/{cheque}/return', [ChequeController::class, 'returnStore'])->name('cheques.return.store');
    Route::resource('transactions', TransactionsController::class);
    Route::resource('bank-accounts', BankAccountController::class);

});

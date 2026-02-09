<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserController;
use App\Livewire\ManageRolesPermissions;
use App\Livewire\UserList;
use App\Livewire\UserForm;
use App\Livewire\CategoryList;
use App\Livewire\FamilyList;
use App\Livewire\GroupList;
use App\Livewire\BrandList;
use App\Livewire\LocationMap;
use App\Livewire\WarehouseLocation;
use App\Livewire\CustomerList;
use App\Livewire\CustomerForm;
use App\Livewire\SupplierList;
use App\Livewire\SupplierForm;
use App\Livewire\ItemList;
use App\Livewire\ItemForm;
use App\Livewire\RestockList;
use App\Livewire\TransactionLog;
use App\Livewire\POForm;
use App\Livewire\POList;
use App\Livewire\DOList;
use App\Livewire\DOForm;
use App\Livewire\QuotationForm;
use App\Livewire\QuotationList;
use App\Livewire\Report;
use App\Livewire\Dashboard;
use App\Http\Controllers\PrintController;
use App\Livewire\Profile;
use App\Livewire\TransactionReport;
use App\Livewire\ChemicalForm\IBCChemicalForm;
use App\Livewire\ChemicalForm\IncomingQualityControlForm;
use App\Livewire\ChemicalForm\LoadingUnloadingForm;
use App\Livewire\ChemicalForm\ConsumptionDashboard;
use App\Livewire\BatchList;
use App\Livewire\BatchDetails;
use App\Livewire\StockMovementList;
use App\Livewire\StockMovementForm;
use App\Livewire\StealthModeToggle;

//production check
if (app()->environment('production')) {
    URL::forceScheme('https');
}

// Public routes
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/manage-roles-permissions', ManageRolesPermissions::class)->name('manage-roles-permissions');

// Routes with 'auth', 'preventBackHistory', and 'switchdb' middleware
Route::middleware(['auth', 'preventBackHistory', 'switchdb'])->group(function () {
    // Switch DB (session-based)
    Route::post('/switch-db', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'connection' => 'required|in:ups,urs,ucs,ups2,urs2,ucs2',
        ]);

        $connection = $request->input('connection');

        // Check if user has access to this company
        if (!\App\Helpers\CompanyAccess::canAccessCompany($connection, auth()->user())) {
            abort(403, 'You do not have access to this company.');
        }

        session(['active_db' => $connection]);
        // Persist session immediately to ensure subsequent concurrent requests see the new DB
        $request->session()->save();

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        // Use 303 redirect to force GET and avoid caching POST response in some clients
        return redirect()->route('dashboard')->setStatusCode(303);
    })->name('switch-db');
    // General dashboard route
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    

    // Profile routes
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });


        // User management routes
        Route::prefix('users')->name('users')->middleware('permission:Manage User')->group(function () {
            Route::get('/', UserList::class);
            Route::get('/add', UserForm::class)->name('.add');
            Route::get('/{user}/view', UserForm::class)->name('.view');
            Route::get('/{user}/edit', UserForm::class)->name('.edit');
        });

        //Company profile route
        Route::prefix('profiles')->name('profiles')->middleware('permission:Edit Company Profile')->group(function () {
            Route::get('/', Profile::class);
        });

        // Stealth Mode Toggle - Super Admin only
        Route::get('/stealth-mode', StealthModeToggle::class)->name('stealth-mode');

        // Location management routes
        Route::prefix('locations')->name('locations')->middleware('permission:Manage Location')->group(function () {
            Route::get('/', LocationMap::class);
            Route::get('/add', LocationMap::class)->name('.add');
        });

        // Warehouse management routes
        Route::prefix('warehouses')->name('warehouses')->middleware('permission:Manage Warehouse')->group(function () {
            Route::get('/', WarehouseLocation::class);
            Route::get('/add', WarehouseLocation::class)->name('.add');
        });

    // });

    // Category management routes
    Route::prefix('categories')->name('categories')->middleware('permission:Manage Category')->group(function () {
        Route::get('/', CategoryList::class);
        Route::get('/add', CategoryList::class)->name('.add');
    });

    // Family management routes
    Route::prefix('families')->name('families')->middleware('permission:Manage Family')->group(function () {
        Route::get('/', FamilyList::class);
        Route::get('/add', FamilyList::class)->name('.add');
    });

    // Group management routes
    Route::prefix('groups')->name('groups')->middleware('permission:Manage Group')->group(function () {
        Route::get('/', GroupList::class);
        Route::get('/add', GroupList::class)->name('.add');
    });

    // Brand management routes
    Route::prefix('brands')->name('brands')->middleware('permission:Manage Brand')->group(function () {
        Route::get('/', BrandList::class);
        Route::get('/add', BrandList::class)->name('.add');
    });

    // Supplier management routes
    Route::prefix('suppliers')->name('suppliers')->middleware('permission:Manage Supplier')->group(function () {
        Route::get('/', SupplierList::class);
        Route::get('/add', SupplierForm::class)->name('.add');
        Route::get('/{supplier}/view', SupplierForm::class)->name('.view');
        Route::get('/{supplier}/edit', SupplierForm::class)->name('.edit');
    });

    // Customer management routes
    Route::prefix('customers')->name('customers')->middleware('permission:Manage Customer')->group(function () {
        Route::get('/', CustomerList::class);
        Route::get('/add', CustomerForm::class)->name('.add');
        Route::get('/{customer}/view', CustomerForm::class)->name('.view');
        Route::get('/{customer}/edit', CustomerForm::class)->name('.edit');
    });

    Route::prefix('items')->name('items')->middleware('permission:Manage Inventory')->group(function () {
        Route::get('/', ItemList::class);
        Route::get('/add', ItemForm::class)->name('.add');
        Route::get('/{item}/view', ItemForm::class)->name('.view');
        Route::get('/{item}/edit', ItemForm::class)->name('.edit');
    });

        //Restock List routes
    Route::prefix('restock-list')->name('restock-list')->middleware('permission:Manage Restock List')->group(function () {
        Route::get('/', RestockList::class);
    });

    //Transaction Log route
    Route::prefix('transaction-log')->name('transaction-log.')->middleware('permission:View Transaction Log')->group(function () {
        Route::get('/', TransactionLog::class);
    });

    //PO route
    Route::prefix('purchase-orders')->name('purchase-orders')->middleware('permission:Manage PO')->group(function () {
        Route::get('/', POList::class);
        Route::get('/add', POForm::class)->name('.add');
        Route::get('/{purchaseOrder}/view', POForm::class)->name('.view');
        Route::get('/{purchaseOrder}/edit', POForm::class)->name('.edit');
    });

    //DO route
    Route::prefix('delivery-orders')->name('delivery-orders')->middleware('permission:Manage DO')->group(function () {
        Route::get('/', DOList::class);
        Route::get('/add', DOForm::class)->name('.add');
        Route::get('/{deliveryOrder}/view', DOForm::class)->name('.view');
        Route::get('/{deliveryOrder}/edit', DOForm::class)->name('.edit');
    });

    // Quotation routes
    Route::prefix('quotations')->name('quotations')->middleware('permission:Manage DO')->group(function () {
        Route::get('/', QuotationList::class);
        Route::get('/add', QuotationForm::class)->name('.add');
        Route::get('/{quotation}/view', QuotationForm::class)->name('.view');
        Route::get('/{quotation}/edit', QuotationForm::class)->name('.edit');
    });

    //Route to fetch specific item's transaction log
    Route::get('/transaction-log/{itemId?}', TransactionLog::class)
        ->middleware('permission:View Transaction Log')
        ->name('transaction-log.show');
    
    //Route to fetch specific customer's delivery order
    Route::get('/delivery-order/{customerId?}', DOList::class)->middleware('permission:Manage DO')
    ->name('delivery-order');
    
    //Route to fetch specific supplier's purchase order
    Route::get('/purchase-order/{supplierId?}', POList::class)->middleware('permission:Manage PO')
    ->name('purchase-order');

    //Route to fetch specific family's item
    Route::get('/items/family/{familyId?}', ItemList::class)->middleware('permission:Manage Inventory')
    ->name('items.by-family');

    //Route to fetch specific bcategory's brand
    Route::get('/categories/{categoryId?}', BrandList::class)->middleware('permission:Manage Category')
    ->name('categories.by-category');

    //Route to fetch specific location's item
    Route::get('/locations-items/{locationId?}', ItemList::class)->middleware('permission:Manage Location')
    ->name('locations-items');

   //File preview and download routes
   Route::prefix('print')->name('print')->group(function () {
        Route::get('/purchase-order/{id}/preview', [PrintController::class, 'previewPO'])->name('.purchase-order.preview');
        Route::get('/delivery-order/{id}/preview', [PrintController::class, 'previewDO'])->name('.delivery-order.preview');
        Route::get('/quotation/{id}/preview', [PrintController::class, 'previewQuotation'])->name('.quotation.preview');
        Route::get('/stock-movement/{id}/preview', [PrintController::class, 'previewStockMovement'])->name('.stock-movement.preview');
        // Route::get('/purchase-order/{id}/download', [PrintController::class, 'download'])->name('.purchase-order.download');
    });

    // Print status routes
    Route::post('/purchase-orders/{id}/mark-printed', [PrintController::class, 'markPOPrinted'])->name('purchase-orders.mark-printed');
    Route::post('/delivery-orders/{id}/mark-printed', [PrintController::class, 'markDOPrinted'])->name('delivery-orders.mark-printed');
    Route::post('/delivery-orders/{id}/post', [PrintController::class, 'postDO'])->name('delivery-orders.post');
    Route::post('/quotations/{id}/mark-printed', [PrintController::class, 'markQuotationPrinted'])->name('quotations.mark-printed');

    //Report section
    Route::prefix('report')->name('report')->middleware('permission:View Report')->group(function () {
        Route::get('/', Report::class);
    });
    Route::prefix('transaction-report')->name('transaction-report')->middleware('permission:View Report')->group(function () {
        Route::get('/', TransactionReport::class);
    });

    //Dashboard section
    Route::prefix('dashboard')->name('dashboard')->group(function () {
        Route::get('/', Dashboard::class);
    });

    //Chemical Consumption section
    Route::prefix('chemical')->name('chemical')->middleware('permission:View Consumption Form')->group(function () {
        Route::get('/IBC-chemical', IBCChemicalForm::class)->name('.ibc');
        Route::get('/loading-unloading', LoadingUnloadingForm::class)->name('.loading-unloading');
        Route::get('/incoming-quality-control', IncomingQualityControlForm::class)->name('.iqc');
        Route::get('/consumption-dashboard', ConsumptionDashboard::class)->name('.consumption-dashboard');
    });




    // Stock Movement routes
    Route::prefix('stock-movements')->name('stock-movements')->middleware('permission:Manage Stock Movement (Picking List)')->group(function () {
        Route::get('/', StockMovementList::class);
        Route::get('/add', StockMovementForm::class)->name('.add');
        Route::get('/{stockMovement}/view', StockMovementForm::class)->name('.view');
        Route::get('/{stockMovement}/edit', StockMovementForm::class)->name('.edit');
    });

    //Excel Import
    Route::get('/import-excel', [UserController::class, 'showImportForm'])->name('show-import-form');
    Route::post('/import-excel', [UserController::class, 'importExcel'])->name('import-excel');
    
    //Delete Records
    Route::get('/delete-records', [UserController::class, 'showDeleteForm'])->name('show-delete-form');
    Route::post('/delete-records', [UserController::class, 'deleteRecords'])->name('delete-records');

    
});

Route::middleware(['auth'])->middleware('permission:View Batch List')->group(function () {
    // Batch routes
    Route::get('/batches', \App\Livewire\BatchList::class)->name('batch-list');
    Route::get('/batches/{batchNum}', \App\Livewire\BatchDetails::class)->name('batch-details');
});

require __DIR__.'/auth.php';

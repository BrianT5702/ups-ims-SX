<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use App\Helpers\CompanyAccess;
use App\Imports\ItemImport;
use Illuminate\Validation\Rule;
use App\Imports\CustomerImport;
use App\Imports\CustomerSalesmanImport;
use App\Imports\SupplierImport;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Item;
use App\Models\RestockList;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\CustomerSnapshot;
use App\Models\Scopes\StealthModeScope;
use Excel;

class UserController extends Controller
{
    protected $guard = 'web';
    
    /**
     * Display the login view.
     */
    public function createLogin(): View
    {
        return view('auth.login');
    }

    protected function redirectIfNotAuthenticated(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['message' => 'You must be logged in to access this page.']);
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function storeLogin(LoginRequest $request): RedirectResponse
{
    // Authenticate the user
    $request->authenticate();

    // Regenerate the session
    $request->session()->regenerate();

    // Dept 2–only accounts land on UPS2; Department 1 / global admins still default to UPS.
    $user = Auth::user();
    if (CompanyAccess::landsOnDepartment2ByDefault($user)) {
        $request->session()->put('active_db', 'ups2');
    } else {
        $request->session()->put('active_db', 'ups');
    }

    toastr()->success('Logged In Successfully');
    return redirect()->intended(route('dashboard'));
            



    
}



    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function storeRegister(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:users,username'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Show the confirm password view.
     */
    public function showConfirmPassword(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function storeConfirmPassword(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        toastr()->success('Logged Out Successfully');

        return redirect('/');
    }

    public function showImportForm()
    {
        return view('import');
    }

    public function importExcel(Request $request)
    {
        $accessibleCompanies = CompanyAccess::getAccessibleCompanies($request->user());

        $request->validate([
            'file' => ['required', 'array', 'min:1'],
            'file.*' => ['file'],
            'import_type' => 'required|in:items,customers,suppliers,customer_salesman',
            'db_connection' => ['required', Rule::in($accessibleCompanies)],
        ]);

        /** @var array<int, \Illuminate\Http\UploadedFile> $uploadedFiles */
        $uploadedFiles = $request->file('file');

        try {
            $this->applyTenantDatabaseConnection($request->db_connection, $request->user());

            if ($request->import_type === 'items') {
                $itemImporter = new ItemImport(ItemImport::formatForConnection($request->db_connection));
                Excel::import($itemImporter, $uploadedFiles[0]);
                $imported = $itemImporter->getSuccessCount();
                $skipped = $itemImporter->getFailureCount();
                $isDept2Format = ItemImport::formatForConnection($request->db_connection) === ItemImport::FORMAT_CODE_NAME;
                if ($imported === 0 && $skipped > 0) {
                    $formatHint = $isDept2Format
                        ? 'column A = Stock Code, column B = Stock Name, column C = Units, header on row 1, data from row 2'
                        : 'empty stock code in column A, or file layout does not match the expected format (data from row 4)';

                    return back()->with(
                        'import_error',
                        "No items were imported ({$skipped} row(s) skipped). Common causes: {$formatHint}. See the application log for details."
                    );
                }
                $message = $imported > 0
                    ? "Items imported successfully ({$imported} row(s)" . ($skipped > 0 ? ", {$skipped} skipped" : '') . ').'
                    : 'Import finished; no data rows were processed.';
            } 
            elseif($request->import_type === 'suppliers'){
                Excel::import(new SupplierImport, $uploadedFiles[0]);
                $message = 'Suppliers imported successfully!';
            }
            elseif($request->import_type === 'customer_salesman'){
                $totalUpdated = 0;
                $allMissing = [];
                $fileCount = count($uploadedFiles);
                $successFiles = 0;
                $failures = [];

                foreach ($uploadedFiles as $uploadedFile) {
                    try {
                        $importer = new CustomerSalesmanImport();
                        Excel::import($importer, $uploadedFile);
                        $totalUpdated += $importer->getUpdatedCount();
                        $allMissing = array_merge($allMissing, $importer->getMissingAccounts());
                        $successFiles++;
                    } catch (\Exception $e) {
                        $failures[] = [
                            'file' => $uploadedFile->getClientOriginalName(),
                            'message' => $e->getMessage(),
                        ];
                    }
                }

                if ($successFiles === 0) {
                    $lines = array_map(
                        static fn (array $f) => '"' . $f['file'] . '": ' . $f['message'],
                        $failures
                    );

                    return back()->with(
                        'import_error',
                        'No customer-salesman files were imported. ' . implode(' ', $lines)
                    );
                }

                if ($fileCount === 1 && $successFiles === 1) {
                    $message = "Customer-salesman assignments updated successfully ({$totalUpdated} updated)";
                } elseif ($successFiles === $fileCount) {
                    $message = "Customer-salesman: {$totalUpdated} assignment(s) updated across {$fileCount} file(s).";
                } else {
                    $message = "Customer-salesman: {$totalUpdated} assignment(s) updated ({$successFiles} of {$fileCount} files succeeded). Review skipped files below.";
                }

                $uniqueMissing = array_values(array_unique($allMissing));
                if (!empty($uniqueMissing)) {
                    $message .= ' Missing accounts (not found): ' . implode(', ', $uniqueMissing);
                }

                if (!empty($failures)) {
                    return back()
                        ->with('import_success', $message)
                        ->with('import_customer_salesman_failures', $failures);
                }
            }
            else {
                Excel::import(new CustomerImport, $uploadedFiles[0]);
                $message = 'Customers imported successfully!';
            }
            
            return back()->with('import_success', $message);
        } catch (\Exception $e) {
            return back()->with('import_error', 'Error importing file(s): ' . $e->getMessage());
        }
    }

    public function showDeleteForm()
    {
        if (!auth()->check() || !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Access denied. Super Admin only.');
        }

        return view('delete');
    }

    public function deleteRecords(Request $request)
    {
        if (!$request->user() || !$request->user()->hasRole('Super Admin')) {
            abort(403, 'Access denied. Super Admin only.');
        }

        $accessibleCompanies = CompanyAccess::getAccessibleCompanies($request->user());

        $request->validate([
            'delete_type' => 'required|in:items,customers,suppliers,delivery_orders,quotations,purchase_orders',
            'db_connection' => ['required', Rule::in($accessibleCompanies)],
        ]);

        try {
            $this->applyTenantDatabaseConnection($request->db_connection, $request->user());

            $deletedCount = 0;
            $message = '';

            if ($request->delete_type === 'items') {
                // Delete dependent restock_lists first (no CASCADE on items)
                RestockList::on($request->db_connection)->delete();
                $deletedCount = Item::on($request->db_connection)->count();
                Item::on($request->db_connection)->delete();
                $message = "Successfully deleted {$deletedCount} item(s) from {$request->db_connection} database.";
            } 
            elseif($request->delete_type === 'suppliers'){
                $deletedCount = Supplier::on($request->db_connection)->count();
                // Delete suppliers that don't have associated items or purchase orders
                $suppliers = Supplier::on($request->db_connection)->get();
                $deletedCount = 0;
                foreach ($suppliers as $supplier) {
                    if (!$supplier->items()->exists() && !$supplier->purchaseOrders()->exists()) {
                        $supplier->delete();
                        $deletedCount++;
                    }
                }
                $message = "Successfully deleted {$deletedCount} supplier(s) from {$request->db_connection} database. Some suppliers could not be deleted because they have associated items or purchase orders.";
            }
            elseif ($request->delete_type === 'delivery_orders') {
                DeliveryOrderItem::on($request->db_connection)->forceDelete();
                $query = DeliveryOrder::on($request->db_connection)->withoutGlobalScope(StealthModeScope::class);
                $deletedCount = $query->count();
                $query->forceDelete();
                $message = "Successfully deleted {$deletedCount} delivery order(s) from {$request->db_connection} database.";
            }
            elseif ($request->delete_type === 'quotations') {
                QuotationItem::on($request->db_connection)->forceDelete();
                $query = Quotation::on($request->db_connection)->withoutGlobalScope(StealthModeScope::class);
                $deletedCount = $query->count();
                $query->forceDelete();
                $message = "Successfully deleted {$deletedCount} quotation(s) from {$request->db_connection} database.";
            }
            elseif ($request->delete_type === 'purchase_orders') {
                PurchaseOrderItem::on($request->db_connection)->forceDelete();
                $query = PurchaseOrder::on($request->db_connection)->withoutGlobalScope(StealthModeScope::class);
                $deletedCount = $query->count();
                $query->forceDelete();
                $message = "Successfully deleted {$deletedCount} purchase order(s) from {$request->db_connection} database.";
            }
            else {
                // Delete dependent records first: DOs/quotations reference customer_snapshots, which reference customers
                DeliveryOrderItem::on($request->db_connection)->forceDelete();
                DeliveryOrder::on($request->db_connection)->withoutGlobalScope(StealthModeScope::class)->forceDelete();
                QuotationItem::on($request->db_connection)->forceDelete();
                Quotation::on($request->db_connection)->withoutGlobalScope(StealthModeScope::class)->forceDelete();
                CustomerSnapshot::on($request->db_connection)->delete();
                $deletedCount = Customer::on($request->db_connection)->count();
                Customer::on($request->db_connection)->delete();
                $message = "Successfully deleted {$deletedCount} customer(s) from {$request->db_connection} database.";
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting records: ' . $e->getMessage());
        }
    }

    /**
     * Switch session and default connection to a tenant database the user may access.
     */
    private function applyTenantDatabaseConnection(string $connection, ?User $user = null): void
    {
        if (!CompanyAccess::canAccessCompany($connection, $user)) {
            abort(403, 'You do not have access to this company database.');
        }

        if (!array_key_exists($connection, config('database.connections'))) {
            abort(400, 'Invalid database connection.');
        }

        session(['active_db' => $connection]);
        config(['database.default' => $connection]);
        \DB::setDefaultConnection($connection);
        \DB::purge($connection);
        \DB::reconnect($connection);
    }

}

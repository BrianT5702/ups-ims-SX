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
use App\Imports\ItemImport;
use App\Imports\CustomerImport;
use App\Imports\SupplierImport;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Item;
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
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'import_type' => 'required|in:items,customers,suppliers',
            'db_connection' => 'required|in:ups,urs,ucs',
        ]);

        try {
            // Set selected DB for this import run
            session(['active_db' => $request->db_connection]);
            config(['database.default' => $request->db_connection]);
            \DB::setDefaultConnection($request->db_connection);
            \DB::purge($request->db_connection);
            \DB::reconnect($request->db_connection);

            if ($request->import_type === 'items') {
                Excel::import(new ItemImport, $request->file('file'));
                $message = 'Items imported successfully!';
            } 
            elseif($request->import_type === 'suppliers'){
                Excel::import(new SupplierImport, $request->file('file'));
                $message = 'Suppliers imported successfully!';
            }else {
                Excel::import(new CustomerImport, $request->file('file'));
                $message = 'Customers imported successfully!';
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function showDeleteForm()
    {
        return view('delete');
    }

    public function deleteRecords(Request $request)
    {
        $request->validate([
            'delete_type' => 'required|in:items,customers,suppliers',
            'db_connection' => 'required|in:ups,urs,ucs',
        ]);

        try {
            // Set selected DB for this delete operation
            session(['active_db' => $request->db_connection]);
            config(['database.default' => $request->db_connection]);
            \DB::setDefaultConnection($request->db_connection);
            \DB::purge($request->db_connection);
            \DB::reconnect($request->db_connection);

            $deletedCount = 0;
            $message = '';

            if ($request->delete_type === 'items') {
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
            else {
                $deletedCount = Customer::on($request->db_connection)->count();
                Customer::on($request->db_connection)->delete();
                $message = "Successfully deleted {$deletedCount} customer(s) from {$request->db_connection} database.";
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting records: ' . $e->getMessage());
        }
    }

}

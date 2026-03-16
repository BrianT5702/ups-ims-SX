<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
            <i class="fas fa-file-excel text-emerald-600"></i>
            Import Data from Excel
        </h2>
    </x-slot>

    {{-- Import-only banners: use import_success/import_error so toastr doesn't intercept them.
         Fixed bottom-right so they're never covered by the nav. --}}
    @if ($errors->any())
        <div class="fixed bottom-6 right-6 z-[9999] max-w-md w-full mx-4 shadow-xl rounded-lg overflow-hidden" role="alert">
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong class="text-red-800 dark:text-red-200">Import failed:</strong>
                        <ul class="mt-2 list-disc list-inside text-red-700 dark:text-red-300 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session('import_error'))
        <div class="fixed bottom-6 right-6 z-[9999] max-w-md w-full mx-4 shadow-xl rounded-lg overflow-hidden" role="alert">
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500 flex-shrink-0"></i>
                <span>{{ session('import_error') }}</span>
            </div>
        </div>
    @endif

    @if (session('import_success'))
        <div class="fixed bottom-6 right-6 z-[9999] max-w-md w-full mx-4 shadow-xl rounded-lg overflow-hidden" role="alert">
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-600 flex-shrink-0"></i>
                <span>{{ session('import_success') }}</span>
            </div>
        </div>
    @endif

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Main form card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl">
                <form action="{{ route('import-excel') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
                    @csrf

                    <div class="space-y-6">
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="db_connection" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database</label>
                                <select name="db_connection" id="db_connection" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">-- Choose DB --</option>
                                    <option value="ups" {{ session('active_db') === 'ups' ? 'selected' : '' }}>UPS</option>
                                    <option value="urs" {{ session('active_db') === 'urs' ? 'selected' : '' }}>URS</option>
                                    <option value="ucs" {{ session('active_db') === 'ucs' ? 'selected' : '' }}>UCS</option>
                                </select>
                            </div>
                            <div>
                                <label for="import_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Import Type</label>
                                <select name="import_type" id="import_type" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="items" {{ old('import_type') === 'items' ? 'selected' : '' }}>Import Items</option>
                                    <option value="customers" {{ old('import_type') === 'customers' ? 'selected' : '' }}>Import Customers</option>
                                    <option value="suppliers" {{ old('import_type') === 'suppliers' ? 'selected' : '' }}>Import Suppliers</option>
                                    <option value="customer_salesman" {{ old('import_type') === 'customer_salesman' ? 'selected' : '' }}>Import Customer-Salesman</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Excel File</label>
                            <div class="flex items-center gap-4">
                                <input type="file" name="file" id="file" required accept=".xlsx,.xls"
                                    class="block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-300 dark:hover:file:bg-emerald-900/50 cursor-pointer">
                            </div>
                        </div>

                        {{-- Format info panels (shown based on import type) --}}
                        <div id="item-format-info" class="format-panel hidden p-5 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                                <i class="fas fa-list-ul text-emerald-600"></i>
                                Item Import Format
                            </h3>
                            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Required columns:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>A: Stock Code (code1)</li>
                                        <li>B: Description</li>
                                        <li>F: Stock/Quantity (defaults to 0 if empty)</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Classification columns:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>C: Category, D: Family, E: Group (uses "UNDEFINED" if not found)</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Price columns (defaults to 0 if empty):</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>G: Cost, H: Cash Price, I: Term Price, J: Customer Price</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Unit of measure:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>K: UOM (defaults to "UNIT" if empty)</li>
                                    </ul>
                                </div>
                                <p class="text-xs pt-2 border-t border-gray-200 dark:border-gray-600 mt-3">
                                    <strong>Note:</strong> Import starts from row 4. Header row is at row 3. At minimum, Description (column B) must be provided. Supplier, warehouse, and location use default values.
                                </p>
                            </div>
                        </div>

                        <div id="customer-format-info" class="format-panel hidden p-5 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                                <i class="fas fa-users text-emerald-600"></i>
                                Customer Import Format
                            </h3>
                            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Required: A: Account, B: Name, C: Address Line 1</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Optional: D–P (Address lines, Contact, Phone, Fax, Email, Class, Area, Term, Reg No, GST, Currency)</p>
                                </div>
                                <p class="text-xs pt-2 border-t border-gray-200 dark:border-gray-600 mt-3">
                                    <strong>Note:</strong> Import starts from row 6.
                                </p>
                            </div>
                        </div>

                        <div id="supplier-format-info" class="format-panel hidden p-5 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                                <i class="fas fa-truck text-emerald-600"></i>
                                Supplier Import Format
                            </h3>
                            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Required: B: Account, C: Name, D: Address</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Optional: F: Reg No, G: GST No, I: Tel & Fax</p>
                                </div>
                                <p class="text-xs pt-2 border-t border-gray-200 dark:border-gray-600 mt-3">
                                    <strong>Note:</strong> Import starts from row 9. Tel & Fax: separate with "/", "|", or ",".
                                </p>
                            </div>
                        </div>

                        <div id="customer-salesman-format-info" class="format-panel hidden p-5 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                                <i class="fas fa-user-tie text-emerald-600"></i>
                                Customer-Salesman Import Format
                            </h3>
                            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <p>Assigns one salesperson (from row 6) to all listed customer accounts.</p>
                                <ul class="list-disc list-inside space-y-1 ml-2">
                                    <li>Row 6, Column D: <strong>SALESMAN: CODE</strong></li>
                                    <li>Data starts at Row 9, Column B: Account (required)</li>
                                </ul>
                                <p class="text-xs pt-2 border-t border-gray-200 dark:border-gray-600 mt-3">
                                    Only existing customer accounts are updated. Selected database (UPS/URS/UCS) determines which customers.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center gap-4">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                            <i class="fas fa-upload"></i>
                            Import
                        </button>
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium rounded-lg transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('import_type').addEventListener('change', function() {
            document.querySelectorAll('.format-panel').forEach(el => el.classList.add('hidden'));
            const panels = {
                items: 'item-format-info',
                customers: 'customer-format-info',
                suppliers: 'supplier-format-info',
                customer_salesman: 'customer-salesman-format-info'
            };
            const id = panels[this.value];
            if (id) document.getElementById(id).classList.remove('hidden');
        });

        (function() {
            const current = "{{ old('import_type', 'items') }}";
            document.getElementById('import_type').value = current;
            document.getElementById('import_type').dispatchEvent(new Event('change'));
        })();
    </script>
</x-app-layout>

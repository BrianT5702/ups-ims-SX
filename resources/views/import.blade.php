<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel</title>
</head>
<body>
    <h1>Import Data from Excel</h1>

    @if ($errors->any())
        <div style="padding: 10px; background: #fdecea; color: #b71c1c; margin-bottom: 12px;">
            <strong>Import failed:</strong>
            <ul style="margin: 6px 0 0 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div style="padding: 10px; background: #fdecea; color: #b71c1c; margin-bottom: 12px;">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div style="padding: 10px; background: #e8f5e9; color: #1b5e20; margin-bottom: 12px;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('import-excel') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="db_connection">Select Database:</label>
            <select name="db_connection" id="db_connection" required>
                <option value="">-- Choose DB --</option>
                <option value="ups" {{ session('active_db') === 'ups' ? 'selected' : '' }}>UPS</option>
                <option value="urs" {{ session('active_db') === 'urs' ? 'selected' : '' }}>URS</option>
                <option value="ucs" {{ session('active_db') === 'ucs' ? 'selected' : '' }}>UCS</option>
            </select>
        </div>
        <div>
            <label for="import_type">Select Import Type:</label>
            <select name="import_type" id="import_type" required>
                <option value="items" {{ old('import_type') === 'items' ? 'selected' : '' }}>Import Items</option>
                <option value="customers" {{ old('import_type') === 'customers' ? 'selected' : '' }}>Import Customers</option>
                <option value="suppliers" {{ old('import_type') === 'suppliers' ? 'selected' : '' }}>Import Suppliers</option>
                <option value="customer_salesman" {{ old('import_type') === 'customer_salesman' ? 'selected' : '' }}>Import Customer-Salesman</option>
            </select>
        </div>
        
        <div style="margin-top: 15px;">
            <label for="file">Choose Excel file:</label>
            <input type="file" name="file" required>
        </div>
        
        <div id="item-format-info" style="display: block; margin-top: 10px; padding: 10px; background-color: #f0f0f0;">
            <h3>Item Import Format:</h3>
            <p>Required columns (Excel column reference):</p>
            <ul>
                <li>A: Stock Code (code1)</li>
                <li>B: Description</li>
                <li>F: Stock/Quantity (defaults to 0 if empty)</li>
            </ul>
            <p>Required classification columns:</p>
            <ul>
                <li>C: Category (uses "UNDEFINED" if not found)</li>
                <li>D: Family (uses "UNDEFINED" if not found)</li>
                <li>E: Group (uses "UNDEFINED" if not found)</li>
            </ul>
            <p>Price columns (defaults to 0 if empty):</p>
            <ul>
                <li>G: Cost</li>
                <li>H: Cash Price</li>
                <li>I: Term Price</li>
                <li>J: Customer Price</li>
            </ul>
            <p><strong>Note:</strong> Import starts from row 4. Header row is at row 3. Data starts from column A. At minimum, the Description (column B) must be provided. Missing category, family, or group will be set to "UNDEFINED". All price fields (Cost, Cash, Term, Customer) will be imported from the Excel file. Supplier, warehouse, and location will use default values.</p>
        </div>
        
        <div id="customer-format-info" style="display: none; margin-top: 10px; padding: 10px; background-color: #f0f0f0;">
            <h3>Customer Import Format:</h3>
            <p>Required columns (Excel column reference):</p>
            <ul>
                <li>A: Account</li>
                <li>B: Name</li>
                <li>C: Address Line 1</li>
            </ul>
            <p>Optional columns:</p>
            <ul>
                <li>D: Address Line 2</li>
                <li>E: Address Line 3</li>
                <li>F: Address Line 4</li>
                <li>G: Contact Person Name</li>
                <li>H: Phone Number</li>
                <li>I: Fax Number</li>
                <li>J: Email</li>
                <li>K: Class (mapped to Pricing Tier)</li>
                <li>L: Area</li>
                <li>M: Term</li>
                <li>N: Business Registration No</li>
                <li>O: GST Registration No</li>
                <li>P: Currency (default: RM)</li>
            </ul>
            <p><strong>Note:</strong> Import starts from row 6. Contact Person Name (Column G) is read but not stored in the database.</p>
        </div>
        
        <div id="supplier-format-info" style="display: none; margin-top: 10px; padding: 10px; background-color: #f0f0f0;">
            <h3>Supplier Import Format:</h3>
            <p>Required columns (Excel column reference):</p>
            <ul>
                <li>B: Account</li>
                <li>C: Name</li>
                <li>D: Address</li>
            </ul>
            <p>Optional columns:</p>
            <ul>
                <li>F: Business Registration No</li>
                <li>G: GST Registration No</li>
                <li>I: Tel & Fax (can be separated by "/" or "|" or ",")</li>
            </ul>
            <p><strong>Note:</strong> Import starts from row 9. Data starts from column B. If Tel & Fax (Column J) contains both values, separate them with "/", "|", or ",". If no separator is found, the value will be treated as phone number only.</p>
        </div>

        <div id="customer-salesman-format-info" style="display: none; margin-top: 10px; padding: 10px; background-color: #f0f0f0;">
            <h3>Customer-Salesman Import Format:</h3>
            <p>This import assigns one salesperson (from row 6) to all listed customer accounts.</p>
            <ul>
                <li>Row 6, Column D: Text like <strong>SALESMAN: CODE</strong> (spaces ignored). CODE is matched against the salesperson username.</li>
                <li>Data starts at Row 9.</li>
                <li>Column B: Account (required) — used to match the customer.</li>
                <li>Other columns are ignored for this assignment.</li>
            </ul>
            <p><strong>Notes:</strong></p>
            <ul>
                <li>Only existing customer accounts are updated.</li>
                <li>The selected database (UPS/URS/UCS) determines which customers are updated.</li>
            </ul>
        </div>
        
        <div style="margin-top: 15px;">
            <button type="submit">Import</button>
        </div>
    </form>

    <script>
        document.getElementById('import_type').addEventListener('change', function() {
            // Hide all format info divs first
            document.getElementById('item-format-info').style.display = 'none';
            document.getElementById('customer-format-info').style.display = 'none';
            document.getElementById('supplier-format-info').style.display = 'none';
            document.getElementById('customer-salesman-format-info').style.display = 'none';
            
            // Show the appropriate format info based on selection
            if (this.value === 'items') {
                document.getElementById('item-format-info').style.display = 'block';
            } else if (this.value === 'customers') {
                document.getElementById('customer-format-info').style.display = 'block';
            } else if (this.value === 'suppliers') {
                document.getElementById('supplier-format-info').style.display = 'block';
            } else if (this.value === 'customer_salesman') {
                document.getElementById('customer-salesman-format-info').style.display = 'block';
            }
        });

        // Show the correct info block on page load based on old selection
        (function() {
            const current = "{{ old('import_type', 'items') }}";
            const event = new Event('change');
            document.getElementById('import_type').value = current;
            document.getElementById('import_type').dispatchEvent(event);
        })();
    </script>
</body>
</html>
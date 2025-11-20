<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel</title>
</head>
<body>
    <h1>Import Data from Excel</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
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
                <option value="items">Import Items</option>
                <option value="customers">Import Customers</option>
                <option value="suppliers">Import Suppliers</option>
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
                <li>A: Category Name (uses "UNDEFINED" if not found)</li>
                <li>B: Brand Name (uses "UNDEFINED" if not found)</li>
                <li>C: Item Name</li>
            </ul>
            <p>Other important columns:</p>
            <ul>
                <li>D: Quantity (defaults to 0 if empty)</li>
                <li>E: Cost (defaults to 0 if empty)</li>
                <li>F: Cash Price (defaults to 0 if empty)</li>  
                <li>G: Term Price (defaults to 0 if empty)</li>
                <li>H: Customer Price (defaults to 0 if empty)</li>
                <li>I: Stock Alert Level (defaults to 0 if empty)</li>
                <li>J: Supplier Name (uses first supplier if not found)</li>
                <li>K: Unit of Measure (defaults to "UNIT" if empty)</li>
                <li>L: Item Code (auto-generated if empty)</li>
                <li>M: Warehouse Name (uses first warehouse if not found)</li>
                <li>N: Location Name (uses first location if not found)</li>
                <!-- Image column no longer supported by importer -->
            </ul>
            <p><strong>Note:</strong> At minimum, the Item Name (column C) must be provided. Missing brands or categories will be set to "UNDEFINED".</p>
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
            <p><strong>Note:</strong> Import starts from row 9. Data starts from column B. If Tel & Fax (Column I) contains both values, separate them with "/" or "|".</p>
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
            
            // Show the appropriate format info based on selection
            if (this.value === 'items') {
                document.getElementById('item-format-info').style.display = 'block';
            } else if (this.value === 'customers') {
                document.getElementById('customer-format-info').style.display = 'block';
            } else if (this.value === 'suppliers') {
                document.getElementById('supplier-format-info').style.display = 'block';
            }
        });
    </script>
</body>
</html>
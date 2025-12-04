<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d32f2f;
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .warning-box h3 {
            color: #856404;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            background-color: #d32f2f;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #b71c1c;
        }
        .info-box {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
        }
        .info-box h3 {
            margin-top: 0;
            color: #333;
        }
        .info-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Delete Records from Database</h1>

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

        <div class="warning-box">
            <h3>⚠️ Warning: This action cannot be undone!</h3>
            <p>This will permanently delete <strong>ALL</strong> records of the selected type from the selected database. Make sure you have a backup if needed.</p>
        </div>

        <form action="{{ route('delete-records') }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete ALL records of this type? This action cannot be undone!');">
            @csrf
            <div class="form-group">
                <label for="db_connection">Select Database:</label>
                <select name="db_connection" id="db_connection" required>
                    <option value="">-- Choose DB --</option>
                    <option value="ups" {{ session('active_db') === 'ups' ? 'selected' : '' }}>UPS</option>
                    <option value="urs" {{ session('active_db') === 'urs' ? 'selected' : '' }}>URS</option>
                    <option value="ucs" {{ session('active_db') === 'ucs' ? 'selected' : '' }}>UCS</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="delete_type">Select Record Type to Delete:</label>
                <select name="delete_type" id="delete_type" required>
                    <option value="">-- Choose Type --</option>
                    <option value="items">Delete All Items</option>
                    <option value="customers">Delete All Customers</option>
                    <option value="suppliers">Delete All Suppliers</option>
                </select>
            </div>

            <div id="delete-info" class="info-box" style="display: none;">
                <h3>What will be deleted:</h3>
                <ul id="delete-details"></ul>
            </div>
            
            <button type="submit">Delete All Records</button>
        </form>
    </div>

    <script>
        document.getElementById('delete_type').addEventListener('change', function() {
            const deleteType = this.value;
            const infoBox = document.getElementById('delete-info');
            const details = document.getElementById('delete-details');
            
            if (deleteType) {
                let info = '';
                if (deleteType === 'items') {
                    info = '<li>All items from the selected database</li><li>This will remove all item records, including their stock quantities, prices, and other details</li>';
                } else if (deleteType === 'customers') {
                    info = '<li>All customers from the selected database</li><li>This will remove all customer records, including their contact information and details</li><li><strong>Note:</strong> Customers with associated Delivery Orders cannot be deleted from the list page, but this bulk delete will attempt to remove all customers</li>';
                } else if (deleteType === 'suppliers') {
                    info = '<li>All suppliers from the selected database</li><li>This will remove all supplier records, including their contact information and details</li><li><strong>Note:</strong> Suppliers with associated Items or Purchase Orders cannot be deleted from the list page, but this bulk delete will attempt to remove all suppliers</li>';
                }
                details.innerHTML = info;
                infoBox.style.display = 'block';
            } else {
                infoBox.style.display = 'none';
            }
        });
    </script>
</body>
</html>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Stock Movement Preview</title>

    <style>
        /* Force consistent rendering across all browsers and settings */
        html {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            text-size-adjust: 100%;
            zoom: 1;
            font-size: 16px; /* Base font size - not affected by browser settings */
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        body {
            font-family: Arial, sans-serif;
            color: #000;
            background-color: #fff;
            font-size: 16px; /* Absolute size */
            line-height: 1.5;
            zoom: 1; /* Force 1:1 zoom */
            transform: scale(1); /* Additional normalization */
            transform-origin: top left;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1000px; /* Wider for letter size - better utilization */
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content {
            padding: 20px;
            flex: 1;
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-info-left {
            text-align: left;
        }

        .company-info-right {
            text-align: right;
        }

        .company-info h2 {
            margin-bottom: 10px;
            color: #333;
        }

        .company-info p {
            margin: 5px 0;
            font-size: 0.9em;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .movement-info {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            margin-bottom: 20px;
        }

        .movement-info-frame {
            border: 1px solid #000;
            padding: 10px;
            width: 65%;
        }

        .movement-info-date {
            text-align: right;
            width: 30%;
        }

        .dotted-line {
            border-bottom: 1px dotted #000;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            border: 1px solid #000;
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .items-table td {
            padding: 12px;
            text-align: left;
            vertical-align: top;
            border: 1px solid #000;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .total-row td {
            border: 1px solid #000;
            padding: 12px;
            background-color: #e9ecef;
        }

        .total {
            font-weight: bold;
        }

        .button-container {
            text-align: right;
            padding: 20px;
            position: relative;
        }

        .print-button {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px; 
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            z-index: 10; 
        }

        .print-button:hover, .back-button:hover {
            background-color: #0056b3;
        }

        /* On-screen reminder for correct print formatting */
        .print-reminder {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 6px 10px;
            font-size: 12px;
            color: #856404;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            z-index: 1000;
        }

        /* Force standard zoom detection warning */
        #zoom-warning {
            position: fixed;
            top: 110px;
            right: 20px;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: bold;
            color: #721c24;
            background: #f8d7da;
            border: 2px solid #f5c6cb;
            border-radius: 4px;
            z-index: 10000;
            display: none;
        }

        @page {
            margin: 0.75cm; /* Slightly smaller margins for more usable space */
            size: letter; /* Changed from A4 to letter size */
        }

        @media print {
            /* Force standardized print settings */
            html {
                zoom: 1 !important;
                font-size: 16px !important;
            }
            
            body {
                background-color: #fff;
                zoom: 1 !important;
                transform: scale(1) !important;
                font-size: 16px !important;
            }
            
            .print-reminder { display: none !important; }
            #zoom-warning { display: none !important; }
            
            @page {
                margin: 0.75cm;
                size: letter;
            }

            .print-button, .back-button {
                display: none !important; 
            }

            .container {
                width: 100%; 
                border: none;
                margin: 0;
                padding: 20px;
            }

            .content {
                overflow: visible;
            }

            .items-table {
                page-break-inside: auto;
            }

            .items-table thead {
                display: table-header-group;
            }

            .items-table tr {
                page-break-inside: avoid;
            }

            .items-table tfoot {
                display: table-row-group;
            }

            .total-row {
                display: table-row !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-reminder">✓ Optimized for Letter Size (8.5" × 11") paper</div>
    <div id="zoom-warning">⚠️ Browser zoom is not 100%! Press Ctrl+0 (Cmd+0 on Mac) to reset zoom for accurate printing.</div>
    <div class="container">
        <button onclick="history.back()" class="back-button">Back</button>
        <div class="content">
            <!-- Header Section -->
            <div class="header-section">
                <h1 style="text-align: center; margin-bottom: 30px; color: #333;">Stock Movement</h1>
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                    <div>
                        <p><strong>Movement ID:</strong> {{ $stockMovement->id }}</p>
                        <p><strong>Reference No:</strong> {{ $stockMovement->reference_no ?? 'N/A' }}</p>
                    </div>
                    <div style="text-align: right;">
                        <p><strong>Date:</strong> {{ $stockMovement->movement_date->format('Y-m-d') }}</p>
                        <p><strong>Time:</strong> {{ $stockMovement->movement_date->format('H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <!-- Movement Information Section -->
            <div class="movement-info" style="border: 1px solid #000; padding: 15px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between;">
                    <div>
                        <p><strong>Movement Type:</strong> {{ $stockMovement->movement_type }}</p>
                        <p><strong>Recorded By:</strong> {{ $stockMovement->user->name }}</p>
                        @if($stockMovement->remarks)
                            <p><strong>Remarks:</strong> {{ $stockMovement->remarks }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stock Movement Details -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stockMovement->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->item_code ?? 'N/A' }}</td>
                        <td>{{ $item->item->item_name ?? 'N/A' }}</td>
                        <td><strong>{{ $item->quantity }}</strong></td>
                        <td>{{ $item->remarks ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="total">Number of Different Items</td>
                        <td colspan="2" class="total">
                            <strong>{{ $stockMovement->items->count() }}</strong>
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" class="total">Total Qty</td>
                        <td colspan="2" class="total">
                            <strong>{{ $stockMovement->items->sum('quantity') }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <button onclick="window.print()" class="print-button">Print</button>

    <script>
        // Force standard zoom level detection and warning
        (function() {
            function checkZoom() {
                // Multiple methods to detect zoom level
                var zoom = Math.round(window.devicePixelRatio * 100);
                var browserZoom = Math.round((window.outerWidth / window.innerWidth) * 100);
                
                // Detect zoom level (accounting for browser differences)
                var detectedZoom = zoom;
                if (browserZoom > 0 && browserZoom !== Infinity) {
                    detectedZoom = browserZoom;
                }
                
                // Show warning if not 100%
                var warning = document.getElementById('zoom-warning');
                if (warning) {
                    if (Math.abs(detectedZoom - 100) > 5) { // 5% tolerance
                        warning.style.display = 'block';
                        warning.textContent = '⚠️ Browser zoom is ' + detectedZoom + '%! Press Ctrl+0 (Cmd+0 on Mac) to reset to 100% for accurate printing.';
                    } else {
                        warning.style.display = 'none';
                    }
                }
            }
            
            // Check zoom on load and resize
            checkZoom();
            window.addEventListener('resize', checkZoom);
            window.addEventListener('load', checkZoom);
            
            // Set body zoom to 100% programmatically
            document.body.style.zoom = "100%";
        })();
    </script>
</body>
</html>

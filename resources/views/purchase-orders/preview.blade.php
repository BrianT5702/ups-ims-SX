<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Purchase Order Preview</title>

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
            /* use default browser font */
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
            max-width: 1000px; /* Wider for letter size */
            margin: 20px auto;
            border: 1px solid #000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            min-height: 100vh; /* Full viewport height for screen view */
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .content {
            padding: 24px 20px 20px;
            flex: 0 0 auto; /* Don't grow */
        }
        

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .company-info-left {
            text-align: left;
            width: 70%;  /* Give more space to company info */
        }
        
        .company-info-left h2 {
            font-size: calc(1.1em + 1px); /* +1px increase */
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .company-info-right {
            text-align: right;
            margin-top: 0;
            width: 28%;  /* Fixed width for right side */
            min-width: 200px; /* Ensure minimum width */
        }

        .company-info-right h2 {
            margin-bottom: 6px;
            white-space: nowrap;
            font-size: calc(1.0em + 1px); /* +1px increase */
            text-transform: uppercase;
        }

        .company-info-right p {
            margin: 2px 0;
            font-size: calc(0.8em + 1px); /* +1px increase */
        }

        .company-info h2 {
            margin-bottom: 6px;
            color: #000; /* Changed from #333 to black */
            font-weight: bold;
            font-size: calc(1.1em + 1px); /* +1px increase */
            white-space: nowrap;
            text-transform: uppercase;
        }

        .company-info p {
            margin: 1px 0;
            font-size: calc(0.8em + 1px); /* +1px increase */
        }

        @media print {
            .company-info h2,
            .company-info-right h2 {
                white-space: nowrap !important;
                font-size: 1.2em !important;
                color: #000 !important; /* Ensure black in print */
            }
        }

        /* Ensure PO top-right info section is fully black */
        .company-info-right { color: #000; }
        .company-info-right h2 { color: #000; }
        .company-info-right p { color: #000; }
        .company-info-right strong { text-transform: uppercase; }
        @media print {
            .company-info-right h2 { color: #000 !important; }
            .company-info-right p { color: #000 !important; }
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .purchase-order-info {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            margin-bottom: 20px;
        }

        .supplier-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .supplier-info-frame {
            border: 1px solid #000;
            padding: 8px;
            width: 100%;
            font-size: 0.9em;
        }

        .supplier-info-date {
            text-align: right;
            width: 30%;
            font-size: 0.9em;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed; /* Fix column widths to prevent squashing */
        }

        .items-table th {
            padding: 10px 10px 6px 10px;
            text-align: left;
            border-bottom: 1px solid #000; /* header underline */
            border-top: 1px solid #000; /* horizontal line above column name */
            font-weight: bold;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
            border-bottom: none;
            font-size: 1.0em;
        }

        /* Fixed column widths (favoring readability and totals) */
        .items-table th:nth-child(1), .items-table td:nth-child(1) { width: 5%; text-align: center; }   /* No. */
        .items-table th:nth-child(2), .items-table td:nth-child(2) { width: 60%; }   /* Description */
        .items-table th:nth-child(3), .items-table td:nth-child(3) { width: 8%; text-align: right; white-space: nowrap; }   /* QTY */
        .items-table th:nth-child(4), .items-table td:nth-child(4) { width: 12%; text-align: right; white-space: nowrap; }   /* Unit Price */
        .items-table th:nth-child(5), .items-table td:nth-child(5) { width: 15%; text-align: right; white-space: nowrap; }   /* Amount */


        .items-table tbody tr:last-child td { border-bottom: none; }

        /* Add padding below the last item row for better spacing */
        .items-table tbody tr:last-child td { padding-bottom: 20px; }

        /* Totals Section Styles */
        .totals-section {
            width: 100%;
            margin-top: 20px;
            border-top: 1px dotted #000;
            padding-top: 10px;
            text-transform: uppercase;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: none;
        }

        .total-row.grand-total {
            border-top: 1px solid #000;
            margin-top: 10px;
            padding-top: 15px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .total-label {
            font-weight: bold;
            text-align: left;
            width: 75%;
        }

        .total-value {
            font-weight: bold;
            text-align: right;
            width: 25%;
            white-space: nowrap;
        }

        .signature-section {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-end !important;
            border-top: 1px solid #000 !important;
            padding: 15px 20px 12px;
            margin-top: auto; /* Push to bottom with flexbox */
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: avoid;
            page-break-before: avoid;
            position: relative;
            font-size: 14px;
            flex: 0 0 auto; /* Don't grow */
        }
        
        .signature-section p, .signature-section strong {
            text-transform: uppercase;
        }

        .signature-left {
            width: 45% !important;
        }

        .signature-right {
            width: 45% !important;
            text-align: center !important;
        }

        .signature-line {
            border-bottom: 1px solid #000 !important;
            margin-top: 40px !important; /* Increased for better signature space */
            margin-bottom: 5px !important;
        }

        .signature-label {
            font-size: 0.9em !important;
            color: #000 !important; /* Changed from #666 to black */
            text-transform: uppercase;
            font-weight: bold !important;
            text-align: center !important;
        }

        @media print {
            html, body {
                height: auto;
                margin: 0;
                padding: 0;
            }
            
            @page {
                margin: 0.75cm;
                size: letter;
            }
            
            .container {
                margin: 0;
                padding: 20px;
                min-height: calc(11in - 1.5cm);
                display: flex !important;
                flex-direction: column !important;
                border: none !important;
                box-shadow: none !important;
                position: relative;
            }
            
            .content {
                padding: 0;
                margin-bottom: 0;
                flex: 1 1 auto;
            }
            
            .signature-section {
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-end !important;
                border-top: 1px solid #000 !important;
                padding: 15px 0 12px !important;
                margin: 0 !important;
                margin-top: var(--signature-spacer, auto) !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                width: 100%;
                box-sizing: border-box;
                flex: 0 0 auto;
                /* Force signature to bottom of last page */
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                z-index: 10;
            }
            .signature-left {
                width: 45% !important;
            }
            .signature-right {
                width: 45% !important;
                text-align: center !important;
            }
            .signature-line {
                border-bottom: 1px solid #000 !important;
                margin-top: 40px !important; /* Better space for signing */
                margin-bottom: 5px !important;
            }
            .signature-label {
                font-size: 0.9em !important;
                color: #000 !important; /* Changed from #666 to black */
                font-weight: bold !important;
                text-align: center !important;
            }
        }

        .signature-left {
            width: 45%;
        }

        .signature-right {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 80px;
            margin-bottom: 5px;
        }

        .signature-lines {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .signature-line-item {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            margin-top: 80px;
        }

        .signature-label {
            font-size: 0.9em;
            color: #666;
        }

        .button-container {
            text-align: right;
            padding: 20px;
            position: relative;
        }

        .print-button {
            position: fixed;
            top: 0;
            right: 0;
            padding: 10px 20px;
            font-size: 17px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            z-index: 1000;
        }

        .back-button {
            position: fixed;
            top: 0;
            left: 0;
            padding: 10px 20px;
            font-size: 17px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            z-index: 1000; 
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
                zoom: 1 !important;
                transform: scale(1) !important;
                font-size: 16px !important;
            }
            
            #zoom-warning { display: none !important; }
            
            @page {
                margin: 0.75cm;
                size: letter;
            }
        }

        /* Smaller font sizes */
        .items-table {
            font-size: 0.95em;
        }
        .items-table th {
            font-size: 0.75em;
            white-space: nowrap;
            overflow: visible;
            text-overflow: unset;
        }
        .items-table td {
            font-size: 1rem;
        }

        @media print {
            body {
                background-color: #fff;
                counter-reset: page;
            }

            .print-button, .back-button {
                display: none !important; 
            }

            .container {
                width: 100%; 
                border: none;
                margin: 0;
                padding: 20px;
                min-height: calc(11in - 1.5cm); /* Letter height minus top/bottom margins */
                display: flex !important; /* Keep flex for signature positioning */
                flex-direction: column !important;
            }
            
            .signature-section {
                flex: 0 0 auto !important; /* Prevent signature from growing */
                margin-top: auto !important; /* Push to bottom */
            }

            /* Hide the on-screen reminder in print */
            .print-reminder { display: none !important; }

            /* Ensure table header repeats on each page */
            .items-table thead { 
                display: table-header-group; 
            }
            
            /* Allow table body to break across pages but keep rows intact */
            .items-table tbody { 
                display: table-row-group;
            }
            
            /* Avoid splitting individual rows */
            .items-table tbody tr { 
                page-break-inside: avoid; 
                break-inside: avoid;
            }
            
            /* Allow the table to break across pages */
            .items-table { 
                page-break-inside: auto;
                break-inside: auto;
            }
            
            /* Keep totals section together and prevent it from breaking */
            .totals-section {
                page-break-inside: avoid;
                break-inside: avoid;
                page-break-before: avoid;
            }
            
            /* Keep individual total rows together */
            .total-row {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            
            /* Enforce column widths in print mode */
            .items-table { table-layout: fixed !important; }
            .items-table th:nth-child(1), .items-table td:nth-child(1) { width: 5% !important; min-width: 5% !important; max-width: 5% !important; }
            .items-table th:nth-child(2), .items-table td:nth-child(2) { width: 60% !important; min-width: 60% !important; max-width: 60% !important; word-wrap: break-word; overflow-wrap: break-word; }
            .items-table th:nth-child(3), .items-table td:nth-child(3) { width: 8% !important; min-width: 8% !important; max-width: 8% !important; white-space: nowrap !important; }
            .items-table th:nth-child(4), .items-table td:nth-child(4) { width: 12% !important; min-width: 12% !important; max-width: 12% !important; white-space: nowrap !important; }
            .items-table th:nth-child(5), .items-table td:nth-child(5) { width: 15% !important; min-width: 15% !important; max-width: 15% !important; white-space: nowrap !important; }
        }
    </style>
</head>
<body>
    <div class="print-reminder">✓ Optimized for Letter Size (8.5" × 11") paper</div>
    <div id="zoom-warning">⚠️ Browser zoom is not 100%! Press Ctrl+0 (Cmd+0 on Mac) to reset zoom for accurate printing.</div>
    <div class="container">
        <button onclick="history.back()" class="back-button">Back</button>
        <div class="content">
            <!-- Company Information Section -->
            <div class="company-info">
                <div class="company-info-left">
                    <h2>{{ $companyProfile->company_name }}</h2>
                    <p>{{ $companyProfile->company_no }} | GST No: {{ $companyProfile->gst_no }}</p>
                    <p>{{ $companyProfile->address_line1 }}</p>
                    @if($companyProfile->address_line2)
                        <p>{{ $companyProfile->address_line2 }}</p>
                    @endif
                    @if($companyProfile->address_line3)
                        <p>{{ $companyProfile->address_line3 }}</p>
                    @endif
                    @if($companyProfile->address_line4)
                        <p>{{ $companyProfile->address_line4 }}</p>
                    @endif
                    <p>Contact Number: {{ $companyProfile->phone_num1 }} 
                        @if($companyProfile->phone_num2)
                            | {{ $companyProfile->phone_num2 }}
                        @endif
                    </p>
                    <p>Fax: {{ $companyProfile->fax_num }} | Email: {{ $companyProfile->email }}</p>
                </div>
                <div class="company-info-right">
                    <h2>Purchase Order</h2>
                    <p><strong>PO No:</strong> {{ $purchaseOrder->po_num }}</p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->date)->format('d/m/Y') }}</p>
                    <p><strong>Reference No:</strong> {{ $purchaseOrder->ref_num ?? '-' }}</p>
                </div>
            </div>

            <!-- Supplier Information Section -->
            <div class="supplier-info">
                <div class="supplier-info-frame">
                    <p><strong>{{ $purchaseOrder->supplierSnapshot->sup_name ?? 'N/A' }}</strong></p>

                    @if($purchaseOrder->supplierSnapshot->address_line1)
                    <p>{{ $purchaseOrder->supplierSnapshot->address_line1 }}</p>
                    @endif
                    @if($purchaseOrder->supplierSnapshot->address_line2)
                        <p>{{ $purchaseOrder->supplierSnapshot->address_line2 }}</p>
                    @endif
                    @if($purchaseOrder->supplierSnapshot->address_line3)
                        <p>{{ $purchaseOrder->supplierSnapshot->address_line3 }}</p>
                    @endif
                    @if($purchaseOrder->supplierSnapshot->address_line4)
                        <p>{{ $purchaseOrder->supplierSnapshot->address_line4 }}</p>
                    @endif
                    <p>Contact Number: {{ $purchaseOrder->supplierSnapshot->phone_num }}</p>
                    @if($purchaseOrder->supplierSnapshot->email)
                    <p>Email: {{ $purchaseOrder->supplierSnapshot->email }}</p>
                    @endif
                </div>
            </div>

            <!-- Purchase Order Details -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Description</th>
                        <th>QTY</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseOrder->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $item->custom_item_name ?? ($item->item->item_name ?? 'N/A') }}
                            @if(!empty($item->more_description))
                                <div style="padding-left: 15px; font-size: 1.0em; color: #000; margin-top: 5px;">
                                    @foreach(explode("\n", $item->more_description) as $line)
                                        @if(trim($line) !== '')
                                            <div>• {{ $line }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->total_price_line_item > 0 ? number_format($item->unit_price, 2) : '' }}</td>
                        <td>{{ $item->total_price_line_item > 0 ? number_format($item->total_price_line_item, 2) : '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if(!empty($purchaseOrder->remark))
                <div style="margin: 15px 0 15px 5%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; position: relative; z-index: 5; background: white;">
                    <div style="font-size: 0.9em; line-height: 1.4; color: #000; display: flex;">
                        <span style="font-weight: bold; min-width: 65px; text-transform: uppercase;">Remark:&nbsp;&nbsp;&nbsp;</span>
                        <div style="flex: 1;">{!! nl2br(e($purchaseOrder->remark)) !!}</div>
                    </div>
                </div>
            @endif

            <!-- Totals Section - positioned after table for better print control -->
            <div class="totals-section">
                @php($poCurrency = $purchaseOrder->supplierSnapshot->currency ?? 'MYR')
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span class="total-value">{{ $poCurrency }} {{ number_format($purchaseOrder->final_total_price ?? 0, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Tax ({{ number_format($purchaseOrder->tax_rate ?? 0, 2) }}%)</span>
                    <span class="total-value">{{ $poCurrency }} {{ number_format($purchaseOrder->tax_amount ?? 0, 2) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span class="total-label">TOTAL</span>
                    <span class="total-value">{{ $poCurrency }} {{ number_format($purchaseOrder->grand_total ?? ($purchaseOrder->final_total_price + ($purchaseOrder->tax_amount ?? 0)), 2) }}</span>
                </div>
            </div>

        </div>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-left">
                <div class="signature-line"></div>
                <p class="signature-label">Company Chop & Signature</p>
            </div>
            <div class="signature-right">
                <p><strong>{{ $companyProfile->company_name }}</strong></p>
                <br><br><br>
                <div class="signature-line"></div>
                <p class="signature-label">(Authorized Signature)</p>
            </div>
        </div>
    </div>

    <button type="button" onclick="triggerPrint()" class="print-button">Print</button>

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

        function triggerPrint() {
            try { updateSignatureSpacer(); } catch (e) {}
            // Mark as printed before opening print dialog
            fetch('{{ route('purchase-orders.mark-printed', $purchaseOrder->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function() {
                setTimeout(function(){ 
                    try { updateSignatureSpacer(); } catch (e) {}
                    try { positionSignatureAtLastPageBottom(); } catch (e) {}
                    setTimeout(function(){ window.print(); }, 150);
                }, 50);
            }).catch(function(error) {
                console.error('Failed to mark as printed:', error);
                setTimeout(function(){ 
                    try { updateSignatureSpacer(); } catch (e) {}
                    try { positionSignatureAtLastPageBottom(); } catch (e) {}
                    setTimeout(function(){ window.print(); }, 150);
                }, 50);
            });
        }
    </script>

    <script>
        // More accurate measurement using real CSS units to align to page frames
        function measurePx(value) {
            var probe = document.createElement('div');
            probe.style.position = 'absolute';
            probe.style.visibility = 'hidden';
            probe.style.height = value;
            document.body.appendChild(probe);
            var px = probe.offsetHeight;
            document.body.removeChild(probe);
            return px;
        }

        function getContentHeightPx() {
            // Letter height minus @page margins (0.75cm top + bottom)
            var letterPx = measurePx('11in');
            var marginPx = measurePx('0.75cm');
            var contentPx = letterPx - (marginPx * 2);
            return Math.max(1, Math.round(contentPx));
        }

        function positionSignatureAtLastPageBottom() {}

        function measurePx(value) {
            var probe = document.createElement('div');
            probe.style.position = 'absolute';
            probe.style.visibility = 'hidden';
            probe.style.height = value;
            document.body.appendChild(probe);
            var px = probe.offsetHeight;
            document.body.removeChild(probe);
            return px;
        }

        function getContentHeightPx() {
            var letterPx = measurePx('11in');
            var marginPx = measurePx('0.75cm');
            return Math.max(1, Math.round(letterPx - (marginPx * 2)));
        }

        function updateSignatureSpacer() {
            try {
                var signature = document.querySelector('.signature-section');
                var remark = document.querySelector('div[style*="border: 1px solid #ddd"]');
                if (!signature) return;
                var pageHeight = getContentHeightPx();
                var content = document.querySelector('.content');
                if (!content) return;
                
                // Calculate total content height including remark
                var contentHeight = content.offsetHeight;
                if (remark) {
                    var remarkRect = remark.getBoundingClientRect();
                    var contentRect = content.getBoundingClientRect();
                    var remarkBottom = remarkRect.bottom - contentRect.top + contentRect.top;
                    contentHeight = Math.max(contentHeight, remarkBottom);
                }
                
                var totalPages = Math.ceil(contentHeight / pageHeight);
                
                // Position signature at bottom of last page, ensuring it doesn't cover remark
                var lastPageTop = (totalPages - 1) * pageHeight;
                var sigHeight = Math.max(1, Math.round(signature.offsetHeight));
                var desiredTop = lastPageTop + (pageHeight - sigHeight);
                
                // If remark exists, ensure signature is below it
                if (remark) {
                    var remarkRect = remark.getBoundingClientRect();
                    var containerRect = document.querySelector('.container').getBoundingClientRect();
                    var remarkBottom = remarkRect.bottom - containerRect.top;
                    if (desiredTop < remarkBottom + 20) { // Add 20px buffer
                        desiredTop = remarkBottom + 20;
                    }
                }
                
                // Set the signature position
                signature.style.position = 'absolute';
                signature.style.top = desiredTop + 'px';
                signature.style.left = '0';
                signature.style.right = '0';
                signature.style.bottom = 'auto';
                signature.style.background = 'white';
                signature.style.zIndex = '10';
                
                // Remove any existing spacer
                document.documentElement.style.removeProperty('--signature-spacer');
            } catch (e) {}
        }

        (function() {
            window.addEventListener('resize', function(){ requestAnimationFrame(updateSignatureSpacer); });
            document.addEventListener('DOMContentLoaded', function(){ requestAnimationFrame(updateSignatureSpacer); });
            setTimeout(updateSignatureSpacer, 100);
            setTimeout(updateSignatureSpacer, 300);
            if (window.matchMedia) {
                var mq = window.matchMedia('print');
                mq.addEventListener ? mq.addEventListener('change', function(e){ if (e.matches) { updateSignatureSpacer(); } }) : mq.addListener(function(e){ if (e.matches) { updateSignatureSpacer(); } });
            }
            window.addEventListener('beforeprint', updateSignatureSpacer);
            window.addEventListener('afterprint', function(){ document.documentElement.style.removeProperty('--signature-spacer'); });
        })();
    </script>
</body>
</html>
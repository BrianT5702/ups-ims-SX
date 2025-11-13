<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Delivery Order Preview</title>

    <style>
        /* Force consistent rendering across all browsers and settings */
        html {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            text-size-adjust: 100%;
            zoom: 1;
            font-size: 16px; /* Base font size - not affected by browser settings */
        }

        :root {
            /* Single source of truth for Qty column width to keep vertical line aligned */
            --qty-col-width: 100px;
            /* Offset to account for column padding differences */
            --col-align-offset: -5px;
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
            min-height: 100vh;
            font-size: 15px; /* Slightly smaller for more rows */
            line-height: 1.45;
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

        /* Vertical line positioned relative to table area to separate columns */
.print-page::after {
    content: '';
    position: absolute;
    left: calc(var(--qty-col-width) + var(--col-align-offset));
    top: var(--vline-start, 0);
    bottom: var(--vline-end, 0);
    width: 1px;
    background: #000;
    pointer-events: none;
    z-index: 2;
    opacity: 1;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}

        .table-area {
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .print-page .table-area {
            flex: 0 0 auto;
        }

        .content {
            padding: 24px 20px 20px;
            flex: 1;
            position: relative; /* Ensure vertical line positions correctly */
            min-height: calc(100vh - 200px); /* Provide enough height for vertical line */
            display: flex;
            flex-direction: column;
        }

        .container {
            position: relative; /* Move vertical line to container level */
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .company-info-left {
            text-align: left;
            width: 70%; /* Match PO width */
        }
        
        .company-info-left h2 {
            font-size: calc(1.1em + 1px); /* +1px increase */
            margin-bottom: 8px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .company-info-right {
            text-align: right;
            margin-top: 0; /* Align with company name like PO */
            width: 28%; /* Match PO width */
            min-width: 200px; /* Match PO width */
        }

        .company-info h2 {
            margin-bottom: 6px;
            color: #000; /* Changed from #333 to black */
            font-weight: bold;
            font-size: calc(1.1em + 1px); /* +1px increase - match PO */
            white-space: nowrap;
            text-transform: uppercase;
        }

        /* Right DO info heading to match PO */
        .company-info-right h2 {
            margin-bottom: 6px;
            white-space: nowrap;
            font-weight: 700;
            font-size: calc(1.0em + 1px); /* +1px increase */
            text-transform: uppercase;
        }

        .company-info p {
            margin: 0;
            font-size: calc(0.78em + 1px);
        }

        @media print {
            .company-info h2,
            .company-info-right h2 {
                white-space: nowrap !important;
                font-size: 1.2em !important;
                color: #000 !important; /* Ensure black in print */
            }
        }

        /* Top-right details: ensure black and match PO styling */
        .company-info-right { color: #000; }
        .company-info-right h2 { color: #000; }
        .company-info-right p { color: #000; margin: 2px 0; font-size: calc(0.8em + 1px); /* +1px increase */ }
        .company-info-right strong { text-transform: uppercase; }

        .customer-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .customer-info-frame {
            border: 1px solid #000;
            padding: 6px;
            width: 100%;
            font-size: 0.88em;
        }

        /* Table styles matching PO */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0; /* keep tight to allow continuous vertical line */
            padding-bottom: 0;
            font-size: 0.92em;
            table-layout: fixed;
            position: relative;
        }


        .items-table th,
        .items-table td {
            padding: 5px 8px;
            text-align: left;
            border-bottom: none;
            font-size: 0.9em;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
        }

        /* Slightly larger font for item rows */
        .items-table td { font-size: 0.98rem; }

        .items-table th {
            font-weight: bold;
            font-size: 0.82em;
            padding: 6px 8px 5px 8px;
            border-bottom: 1px solid #000;
            border-top: 1px solid #000; /* horizontal line above column name */
            vertical-align: middle;
            text-transform: uppercase;
        }

        /* Fixed column widths (favoring more space for Item Name) */
        .items-table th:nth-child(1), .items-table td:nth-child(1) { width: var(--qty-col-width); }   /* QTY */
        .items-table th:nth-child(2), .items-table td:nth-child(2) { width: auto; }   /* Item Name - fill all remaining space */
        
        /* Ensure consistent padding and alignment */
        .items-table th:nth-child(1), .items-table td:nth-child(1) { padding-right: 4px; }
        .items-table th:nth-child(2), .items-table td:nth-child(2) { padding-left: 8px; }

        /* Vertical separator between Qty and Description - removed, using container::after instead */
        
        /* Align bullet points with item name */
        .items-table td:nth-child(2) div {
            padding-left: 0 !important;
            margin-top: 5px;
        }


        .pages-container {
            display: none;
            flex-direction: column;
            gap: 30px;
            width: 100%;
        }

        .pages-container[data-measuring="true"] .print-page {
            min-height: auto !important;
        }

        .print-page {
            display: flex;
            flex-direction: column;
            position: relative;
            min-height: calc(11in - (0.75cm * 2));
            page-break-after: always;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .print-page--first {
            margin-top: 20px;
        }

        .print-page--last {
            page-break-after: auto;
        }

        .print-page-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
            flex: 1 1 auto;
        }

        .print-page-footer {
            margin-top: auto;
            padding-top: 18px;
            flex: 0 0 auto;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #000;
            padding: 16px 0 12px;
            page-break-inside: avoid;
            break-inside: avoid;
            font-size: 13px;
            width: 100%;
        }

        .signature-section p,
        .signature-section strong {
            text-transform: uppercase;
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
            margin-top: 34px;
            margin-bottom: 4px;
        }

        .signature-label {
            font-size: 0.9em;
            color: #000;
            text-transform: uppercase;
            font-weight: bold;
            text-align:center;
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

    .print-page {
        page-break-after: always;
    }

    .print-page--last {
        page-break-after: auto;
    }

    #remark-wrapper {
        margin-left: calc(var(--qty-col-width) + var(--col-align-offset)) !important;
        page-break-inside: avoid;
        break-inside: avoid;
        position: relative;
        z-index: 5;
        background: white;
    }

    .print-page::after {
        display: block !important;
    }
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
                counter-reset: page;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                zoom: 1 !important;
                transform: scale(1) !important;
                font-size: 16px !important;
            }

            .print-button, .back-button {
                display: none !important; 
            }

            .container {
                width: 100%; 
                border: none;
                margin: 0;
                padding: 20px;
                min-height: calc(11in - 1.5cm); /* Match PO/Quotation so signature can stick to bottom */
                padding-bottom: 0; /* Remove bottom padding in print */
                display: flex !important;
                flex-direction: column !important;
            }

            /* Hide the on-screen reminder in print */
            .print-reminder { display: none !important; }
            #zoom-warning { display: none !important; }

            /* Ensure table header repeats on each page */
            .items-table thead {
                display: table-header-group;
            }

            /* Keep rows together where possible */
            .items-table tr {
                page-break-inside: avoid;
            }

            /* Keep footer rows together */
            .items-table tfoot {
                display: table-footer-group;
            }


            .content { 
                overflow: visible; 
                position: relative;
                min-height: auto;
                /* remove extra bottom padding to avoid forcing a new page when content fits */
                padding-bottom: 0;
            }

            .push-footer { 
                flex: 1 1 auto; 
                height: auto; 
            }

            .signature-section { 
                margin-top: 0; 
                page-break-inside: avoid; 
                break-inside: avoid; 
                position: relative;
                bottom: auto;
            }
            /* Spacer uses flex to fill available space */
        }
    </style>
</head>
<body>
    <div class="print-reminder">✓ Optimized for Letter Size (8.5" × 11") paper</div>
    <div id="zoom-warning">⚠️ Browser zoom is not 100%! Press Ctrl+0 (Cmd+0 on Mac) to reset zoom for accurate printing.</div>
    <div class="container">
        <button onclick="goBack()" class="back-button">Back</button>
        <script>
            function goBack() {
                const returnUrl = sessionStorage.getItem('returnToDOList');
                if (returnUrl && returnUrl.includes('/delivery-orders')) {
                    window.location.href = returnUrl;
                } else {
                    window.location.href = '/delivery-orders';
                }
                sessionStorage.removeItem('returnToDOList');
            }
        </script>
        <div class="content">
            <div id="print-source">
                <div class="page-header">
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
                            <h2>Delivery Order</h2>
                            <p><strong>DO No:</strong> {{ $deliveryOrder->do_num }}</p>
                            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($deliveryOrder->date)->format('d/m/Y') }}</p>
                            <p><strong>Reference No:</strong> {{ $deliveryOrder->ref_num ?? '-' }}</p>
                            <p><strong>Customer PO No:</strong> {{ $deliveryOrder->cust_po }}</p>
                            <p><strong>Terms:</strong> {{ $deliveryOrder->customerSnapshot->term ?? $deliveryOrder->customer->term ?? 'N/A' }}</p>
                            <p><strong>Salesman:</strong> {{ $deliveryOrder->salesman->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="customer-info">
                        <div class="customer-info-frame">
                            <p><strong>{{ $deliveryOrder->customerSnapshot->cust_name ?? 'N/A' }}</strong></p>
                            @if($deliveryOrder->customerSnapshot->address_line1)
                            <p>{{ $deliveryOrder->customerSnapshot->address_line1 }}</p>
                            @endif
                            @if($deliveryOrder->customerSnapshot->address_line2)
                                <p>{{ $deliveryOrder->customerSnapshot->address_line2 }}</p>
                            @endif
                            @if($deliveryOrder->customerSnapshot->address_line3)
                                <p>{{ $deliveryOrder->customerSnapshot->address_line3 }}</p>
                            @endif
                            @if($deliveryOrder->customerSnapshot->address_line4)
                                <p>{{ $deliveryOrder->customerSnapshot->address_line4 }}</p>
                            @endif
                            <p>Contact Number: {{ $deliveryOrder->customerSnapshot->phone_num }}</p>
                            <p>@if($deliveryOrder->customerSnapshot->fax_num)Fax: {{ $deliveryOrder->customerSnapshot->fax_num }} | @endif @if($deliveryOrder->customerSnapshot->email)Email: {{ $deliveryOrder->customerSnapshot->email }}@endif</p>
                        </div>
                    </div>
                </div>

                <div class="table-area" id="items-table-source">
                    <table class="items-table" id="items-table">
                        <thead>
                            <tr>
                                <th>QTY</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveryOrder->items as $index => $item)
                            <tr>
                                <td>{{ $item->qty }} {{ $item->item->um ?? 'UNIT' }}</td>
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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!empty($deliveryOrder->remark))
                    @php $lines = explode("\n", $deliveryOrder->remark); @endphp
                    <div id="remark-source">
                        <div style="margin: 0;">
                            <div id="remark-wrapper" style="margin-left: calc(var(--qty-col-width) + var(--col-align-offset)); padding-left: 8px; padding-top: 10px;">
                                <div style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <div style="font-size: 0.92em; line-height: 1.35; color: #000;">
                                        <div style="display: flex;">
                                            <span style="font-weight: bold; min-width: 60px; text-transform: uppercase;">Remark:&nbsp;&nbsp;&nbsp;</span>
                                            <div style="flex: 1;">
                                                <div>{{ $lines[0] }}</div>
                                                @foreach(array_slice($lines, 1) as $line)
                                                    <div style="margin-top: 2px;">{{ $line }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div id="pages-container" class="pages-container"></div>
        </div>

        <div id="signature-template" class="signature-section" style="display: none;">
            <div class="signature-left">
                <p><strong>Recipient Acknowledgment:</strong></p>
                <br><br><br>
                <div class="signature-line"></div>
                <p class="signature-label">Recipient's Signature</p>
            </div>
            <div class="signature-right">
                <br><br><br><br>
                <div class="signature-line"></div>
                <p class="signature-label">Authorised Signature</p>
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
            try { paginateDeliveryOrder(true); } catch (e) {}
            fetch('{{ route('delivery-orders.mark-printed', $deliveryOrder->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function() {
                setTimeout(function(){
                    try { paginateDeliveryOrder(true); } catch (e) {}
                    setTimeout(function(){ window.print(); }, 150);
                }, 50);
            }).catch(function(error) {
                console.error('Failed to mark as printed:', error);
                setTimeout(function(){
                    try { paginateDeliveryOrder(true); } catch (e) {}
                    setTimeout(function(){ window.print(); }, 150);
                }, 50);
            });
        }
    </script>

    <script>
        (function () {
            var pageHeightCache = null;
            var scheduled = false;
            var building = false;

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

            function getPageHeight(force) {
                if (force || !pageHeightCache) {
                    var letterPx = measurePx('11in');
                    var marginPx = measurePx('0.75cm');
                    pageHeightCache = Math.max(1, Math.round(letterPx - (marginPx * 2)));
                }
                return pageHeightCache;
            }

            function removeIds(node) {
                if (!node) {
                    return;
                }
                if (node.removeAttribute) {
                    node.removeAttribute('id');
                }
                node.querySelectorAll('[id]').forEach(function (el) {
                    el.removeAttribute('id');
                });
            }

            function createPage(pagesContainer, headerTemplate, theadTemplate, signatureTemplate, isFirstPage) {
                var page = document.createElement('div');
                page.className = 'print-page';
                if (isFirstPage) {
                    page.classList.add('print-page--first');
                }

                var body = document.createElement('div');
                body.className = 'print-page-body';
                page.appendChild(body);

                if (headerTemplate) {
                    var headerClone = headerTemplate.cloneNode(true);
                    removeIds(headerClone);
                    body.appendChild(headerClone);
                }

                var tableWrapper = document.createElement('div');
                tableWrapper.className = 'table-area';
                var table = document.createElement('table');
                table.className = 'items-table';
                tableWrapper.appendChild(table);
                body.appendChild(tableWrapper);

                if (theadTemplate) {
                    var theadClone = theadTemplate.cloneNode(true);
                    removeIds(theadClone);
                    table.appendChild(theadClone);
                }

                var tbody = document.createElement('tbody');
                table.appendChild(tbody);

                var footerWrapper = document.createElement('div');
                footerWrapper.className = 'print-page-footer';
                var signature = signatureTemplate.cloneNode(true);
                signature.style.display = '';
                removeIds(signature);
                footerWrapper.appendChild(signature);
                page.appendChild(footerWrapper);

                pagesContainer.appendChild(page);

                return {
                    page: page,
                    body: body,
                    tableWrapper: tableWrapper,
                    table: table,
                    tbody: tbody,
                    footer: footerWrapper
                };
            }

            function paginateDeliveryOrder(force) {
                if (building) {
                    return;
                }
                building = true;

                try {
                    var source = document.getElementById('print-source');
                    var pagesContainer = document.getElementById('pages-container');
                    var signatureTemplate = document.getElementById('signature-template');
                    var itemsTable = document.getElementById('items-table');

                    if (!source || !pagesContainer || !signatureTemplate || !itemsTable) {
                        return;
                    }

                    var headerTemplate = source.querySelector('.page-header');
                    var theadTemplate = itemsTable.querySelector('thead');
                    if (!theadTemplate) {
                        return;
                    }

                    var rows = Array.from(itemsTable.querySelectorAll('tbody tr'));
                    var remarkSource = document.getElementById('remark-source');
                    var pageHeight = getPageHeight(force);
                    var tolerance = 4;
                    var isFirstPage = true;
                    var activePage = null;
                    pagesContainer.innerHTML = '';
                    pagesContainer.style.display = 'flex';
                    pagesContainer.setAttribute('data-measuring', 'true');
                    pagesContainer.style.visibility = 'hidden';
                    pagesContainer.style.position = 'absolute';
                    pagesContainer.style.left = '-9999px';
                    pagesContainer.style.right = 'auto';
                    pagesContainer.style.top = '0';

                    function ensurePage() {
                        if (!activePage) {
                            activePage = createPage(pagesContainer, headerTemplate, theadTemplate, signatureTemplate, isFirstPage);
                            isFirstPage = false;
                        }
                    }

                    rows.forEach(function (row) {
                        var clone = row.cloneNode(true);
                        ensurePage();
                        activePage.tbody.appendChild(clone);

                        if (activePage.page.offsetHeight > (pageHeight - tolerance)) {
                            activePage.tbody.removeChild(clone);
                            activePage = null;
                            ensurePage();
                            activePage.tbody.appendChild(clone);
                        }
                    });

                    if (rows.length === 0) {
                        ensurePage();
                    }

                    if (remarkSource) {
                        var remarkClone = remarkSource.cloneNode(true);
                        removeIds(remarkClone);
                        remarkClone.setAttribute('data-page-remark', '');

                        ensurePage();
                        activePage.body.appendChild(remarkClone);
                        if (activePage.page.offsetHeight > (pageHeight - tolerance)) {
                            activePage.body.removeChild(remarkClone);
                            activePage = null;
                            ensurePage();
                            activePage.body.appendChild(remarkClone);
                        }
                    }

                    var renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                    renderedPages.forEach(function (page) {
                        var tbody = page.querySelector('tbody');
                        var hasRows = tbody && tbody.children.length > 0;
                        var hasRemark = page.querySelector('[data-page-remark]');
                        if (!hasRows && !hasRemark) {
                            page.parentNode.removeChild(page);
                        }
                    });

                    renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                    renderedPages.forEach(function (page) {
                        var tableArea = page.querySelector('.table-area');
                        var footer = page.querySelector('.print-page-footer');
                        var header = page.querySelector('.page-header');
                        var headerHeight = header ? header.offsetHeight : 0;
                        var start = tableArea ? tableArea.offsetTop : headerHeight;
                        var end = footer ? footer.offsetTop : page.offsetHeight;
                        page.style.setProperty('--vline-start', start + 'px');
                        page.style.setProperty('--vline-end', Math.max(0, page.offsetHeight - end) + 'px');
                    });

                    if (renderedPages.length > 0) {
                        renderedPages.forEach(function (page) { page.classList.remove('print-page--last'); });
                        renderedPages[renderedPages.length - 1].classList.add('print-page--last');
                    }

                    pagesContainer.style.visibility = '';
                    pagesContainer.style.position = '';
                    pagesContainer.style.left = '';
                    pagesContainer.style.right = '';
                    pagesContainer.style.top = '';
                    pagesContainer.removeAttribute('data-measuring');
                    pagesContainer.style.display = 'flex';
                    source.style.display = 'none';
                } finally {
                    building = false;
                }
            }

            function schedulePaginate() {
                if (scheduled) {
                    return;
                }
                scheduled = true;
                window.requestAnimationFrame(function () {
                    scheduled = false;
                    paginateDeliveryOrder(false);
                });
            }

            window.paginateDeliveryOrder = paginateDeliveryOrder;

            window.addEventListener('load', function () {
                paginateDeliveryOrder(true);
            });

            window.addEventListener('resize', schedulePaginate);

            if (window.matchMedia) {
                var mq = window.matchMedia('print');
                if (mq.addEventListener) {
                    mq.addEventListener('change', function (e) {
                        if (e.matches) {
                            paginateDeliveryOrder(true);
                        }
                    });
                } else if (mq.addListener) {
                    mq.addListener(function (e) {
                        if (e.matches) {
                            paginateDeliveryOrder(true);
                        }
                    });
                }
            }

            window.addEventListener('beforeprint', function () {
                paginateDeliveryOrder(true);
            });
        })();
    </script>
</body>
</html>
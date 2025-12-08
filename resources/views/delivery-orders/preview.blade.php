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
            font-family: Arial, sans-serif; /* Use Arial font */
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
            font-size: 0.8em;
            line-height: 1.3;
        }

        /* Table styles matching Quotation */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0; /* keep tight to allow continuous vertical line */
            padding-bottom: 0;
            font-size: 0.85em;
            table-layout: fixed;
            position: relative;
        }

        .items-table th {
            padding: 6px 8px 4px 8px;
            text-align: left;
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.7em;
            line-height: 1.3;
            vertical-align: middle;
        }

        .items-table td {
            padding: 4px 8px;
            text-align: left;
            border-bottom: none;
            font-size: 0.85em;
            line-height: 1.3;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
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
            gap: 28px;
            width: 100%;
        }

        @media print {
            /* Remove gap between pages in print to prevent blank pages */
            .pages-container {
                gap: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Ensure print pages have no extra spacing */
            .pages-container .print-page {
                margin: 0 !important;
                margin-bottom: 0 !important;
            }
            
            .pages-container .print-page:not(:last-child) {
                margin-bottom: 0 !important;
            }
        }

        /* Page counter display */
        .page-counter {
            position: fixed;
            top: 50px;
            right: 20px;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            color: #0d6efd;
            background: #e7f3ff;
            border: 2px solid #0d6efd;
            border-radius: 4px;
            z-index: 1000;
            display: none;
        }

        .page-counter.show {
            display: block;
        }

        /* Make pages-container pages match container styling on screen for accurate preview */
        .pages-container .print-page {
            background-color: #fff;
            border: 1px solid #000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto 28px;
            max-width: 1000px;
            padding: 20px;
            box-sizing: border-box;
        }

        .pages-container .print-page:last-child {
            margin-bottom: 0;
        }

        /* Make signature padding in pages-container match print (no horizontal padding since page has padding) */
        .pages-container .print-page-footer .signature-section {
            padding: 16px 0 12px !important;
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

        /* Last page shouldn't force a page break after */
        .print-page--last {
            page-break-after: auto;
        }

        /* Page number indicator on each page (screen only) */
        .print-page::before {
            content: 'Page ' attr(data-page-number) ' of ' attr(data-total-pages);
            position: absolute;
            top: -25px;
            left: 0;
            font-size: 12px;
            font-weight: bold;
            color: #666;
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 4px;
            z-index: 100;
        }

        @media print {
            .print-page::before {
                display: none;
            }
        }

        .print-page--first {
            margin-top: 20px;
        }

        @media print {
            /* Remove top margin from first page in print to prevent blank page */
            .print-page--first {
                margin-top: 0 !important;
                page-break-before: auto !important;
            }
            
            /* Ensure first page doesn't have unnecessary page break */
            .pages-container > .print-page:first-child {
                page-break-before: auto !important;
                margin-top: 0 !important;
                padding-top: 0 !important;
            }
            
            /* Ensure pages-container first child has no top margin/padding */
            .pages-container:first-child .print-page--first {
                margin-top: 0 !important;
                padding-top: 20px !important;
            }
            
            /* Ensure signature stays with content on the same page */
            .print-page-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                orphans: 3; /* Keep at least 3 lines with previous content */
                widows: 3; /* Keep at least 3 lines on next page */
            }
            
            .print-page-footer .signature-section {
                page-break-before: avoid !important; /* Keep signature with content - same as quotations */
                page-break-after: avoid !important; /* Prevent signature from being pushed to next page */
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            /* Ensure last page content stays together with signature (same as quotations) */
            .print-page--last .print-page-body {
                page-break-after: avoid !important;
            }
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
        width: 100% !important; 
        border: none !important; 
        box-shadow: none !important;
        margin: 0 !important; 
        padding: 0 !important; 
        min-height: auto !important;
        display: block !important;
        position: relative;
    }
    
    .content {
        padding: 0 !important;
        margin: 0 !important;
        flex: none !important;
    }
    
    /* Ensure pages-container takes full width in print */
    .pages-container {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Print pages should match exactly what's shown in preview */
    .pages-container .print-page {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
        box-sizing: border-box !important;
        min-height: calc(11in - 1.5cm) !important;
    }


    
    /* Prevent blank first page - ensure first page has no page break before */
    .pages-container > .print-page:first-child {
        page-break-before: auto !important;
        margin-top: 0 !important;
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
                font-family: Arial, sans-serif !important;
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
                width: 100% !important; 
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                min-height: auto !important;
                display: block !important;
            }

            /* Hide the on-screen reminder in print */
            .print-reminder { display: none !important; }
            #zoom-warning { display: none !important; }
            .page-counter { display: none !important; }
            
            /* Ensure print-source is hidden and pages-container is visible in print */
            #print-source {
                display: none !important;
            }
            
            .pages-container {
                display: flex !important;
            }
            
            /* Remove border/shadow from pages-container pages in print to match print styling */
            .pages-container .print-page {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 20px !important;
                position: relative !important;
            }
            
            /* Ensure print-page-body and print-page-footer use normal flow */
            .pages-container .print-page-body,
            .pages-container .print-page-footer {
                position: relative !important;
            }
            
            /* Ensure signature section prints on every page - use normal flow */
            .signature-section { 
                display: flex !important; 
                justify-content: space-between !important; 
                align-items: flex-end !important; 
                page-break-inside: avoid !important; 
                break-inside: avoid !important; 
                visibility: visible !important;
                position: relative !important;
            }
            
            /* Ensure signature template is always hidden - it's only used for cloning */
            #signature-template {
                display: none !important;
            }
            
            /* Ensure paginated pages signature uses normal flow and is visible on all pages */
            .pages-container .print-page-footer .signature-section {
                position: relative !important;
                display: flex !important;
                visibility: visible !important;
            }
            
            /* Ensure signature footer is visible on all pages */
            .pages-container .print-page-footer {
                display: block !important;
                visibility: visible !important;
            }

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
    <div id="page-counter" class="page-counter">Calculating pages...</div>
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
                            <p><strong>DO No:</strong> <strong>{{ $deliveryOrder->do_num }}</strong></p>
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
                                    @if(!empty($item->item->details))
                                        <div style="padding-left: 15px; font-size: 1.0em; color: #000; margin-top: 5px;">
                                            @foreach(explode("\n", $item->item->details) as $line)
                                                @if(trim($line) !== '')
                                                    <div>• {{ $line }}</div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
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
                                <div style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;">
                                    <div style="font-size: 0.75em; line-height: 1.3; color: #000;">
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
            // Mark as printed before opening print dialog
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
            
            // Function to update vertical line positions - make it accessible globally
            // Need different calculation methods for screen preview vs print mode
            window.updateVerticalLines = function() {
                var pagesContainer = document.getElementById('pages-container');
                if (!pagesContainer) return;
                
                // Check if we're in print mode
                var isPrintMode = window.matchMedia && window.matchMedia('print').matches;
                
                var renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                renderedPages.forEach(function (page) {
                    var tableArea = page.querySelector('.table-area');
                    var footer = page.querySelector('.print-page-footer');
                    var header = page.querySelector('.page-header');
                    
                    var start = 0;
                    var end = 0;
                    
                    if (tableArea) {
                        if (isPrintMode) {
                            // In print mode: tableArea.offsetTop is relative to print-page-body
                            // We need to add body's offsetTop to get position relative to print-page
                            var body = page.querySelector('.print-page-body');
                            var bodyOffset = body ? body.offsetTop : 0;
                            start = bodyOffset + tableArea.offsetTop;
                        } else {
                            // In screen preview: use getBoundingClientRect for accurate positioning
                            var pageRect = page.getBoundingClientRect();
                            var tableRect = tableArea.getBoundingClientRect();
                            start = tableRect.top - pageRect.top;
                        }
                    } else if (header) {
                        if (isPrintMode) {
                            var body = page.querySelector('.print-page-body');
                            var bodyOffset = body ? body.offsetTop : 0;
                            start = bodyOffset + header.offsetHeight;
                        } else {
                            var pageRect = page.getBoundingClientRect();
                            var headerRect = header.getBoundingClientRect();
                            start = headerRect.bottom - pageRect.top;
                        }
                    }
                    
                    if (footer) {
                        if (isPrintMode) {
                            // In print mode: use offsetTop
                            end = page.offsetHeight - footer.offsetTop;
                        } else {
                            // In screen preview: use getBoundingClientRect
                            var pageRect = page.getBoundingClientRect();
                            var footerRect = footer.getBoundingClientRect();
                            end = pageRect.bottom - footerRect.top;
                        }
                    }
                    
                    page.style.setProperty('--vline-start', start + 'px');
                    page.style.setProperty('--vline-end', Math.max(0, end) + 'px');
                });
            };

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

            function getPageHeight(force, isPrintContext) {
                // Force recalculation when in print mode or when forced
                var isPrintMode = isPrintContext || (window.matchMedia && window.matchMedia('print').matches);
                if (force || !pageHeightCache || isPrintMode) {
                    // Use a more accurate method that accounts for print DPI
                    // In print mode, browsers use different DPI, so we need to recalculate
                    var letterPx = measurePx('11in');
                    var marginPx = measurePx('0.75cm');
                    var calculatedHeight = Math.max(1, Math.round(letterPx - (marginPx * 2)));
                    
                    // Use a lighter reduction so print can fit more lines (keep screen and print identical)
                    // Approx 5% reduction: 0.95
                    pageHeightCache = Math.round(calculatedHeight * 0.95);
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
                    // Check if we're in print context (either print media query or beforeprint event)
                    var isPrintMode = window.matchMedia && window.matchMedia('print').matches;
                    var pageHeight = getPageHeight(force, isPrintMode);
                    // Use the same tolerance for screen and print; allow a bit more room
                    var tolerance = 6;
                    var usableHeight = pageHeight; // Already reduced in getPageHeight
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

                    // DO MUST FIT ON ONE PAGE ONLY - enforce single-page limit
                    var pageExceeded = false;
                    rows.forEach(function (row) {
                        if (pageExceeded) {
                            return; // Stop processing if page already exceeded
                        }
                        var clone = row.cloneNode(true);
                        ensurePage();
                        activePage.tbody.appendChild(clone);

                        // Use same check as quotations - check page height directly
                        // The signature is already part of the page, so offsetHeight includes it
                        if (activePage.page.offsetHeight > (usableHeight - tolerance)) {
                            // DO MUST FIT ON ONE PAGE - remove the row and mark as exceeded
                            activePage.tbody.removeChild(clone);
                            pageExceeded = true;
                            // Show warning that content exceeds one page
                            var warningMsg = '⚠️ Content exceeds one page limit. Please remove items or shorten descriptions to fit on a single page.';
                            if (!document.getElementById('do-page-limit-warning')) {
                                var warningDiv = document.createElement('div');
                                warningDiv.id = 'do-page-limit-warning';
                                warningDiv.style.cssText = 'position: fixed; top: 70px; left: 50%; transform: translateX(-50%); background: #ff6b6b; color: white; padding: 15px 20px; border-radius: 5px; z-index: 10000; box-shadow: 0 4px 6px rgba(0,0,0,0.3); font-weight: bold; max-width: 600px; text-align: center;';
                                warningDiv.textContent = warningMsg;
                                document.body.appendChild(warningDiv);
                                setTimeout(function() {
                                    if (warningDiv.parentNode) {
                                        warningDiv.parentNode.removeChild(warningDiv);
                                    }
                                }, 5000);
                            }
                        }
                    });

                    if (rows.length === 0) {
                        ensurePage();
                    }

                    if (remarkSource && !pageExceeded) {
                        var remarkClone = remarkSource.cloneNode(true);
                        removeIds(remarkClone);
                        remarkClone.setAttribute('data-page-remark', '');

                        ensurePage();
                        activePage.body.appendChild(remarkClone);
                        // Check if page overflows, accounting for signature footer
                        // DO MUST FIT ON ONE PAGE - stricter check
                        var currentPageHeight = activePage.page.offsetHeight;
                        if (currentPageHeight > (usableHeight - tolerance)) {
                            // Allow slight overflow (30px) to keep signature together, but warn if more
                            if (currentPageHeight > (usableHeight + 30)) {
                                // Content exceeds one page - remove remark and show warning
                                activePage.body.removeChild(remarkClone);
                                pageExceeded = true;
                                var warningMsg = '⚠️ Content exceeds one page limit. Please shorten the remark to fit on a single page.';
                                if (!document.getElementById('do-page-limit-warning')) {
                                    var warningDiv = document.createElement('div');
                                    warningDiv.id = 'do-page-limit-warning';
                                    warningDiv.style.cssText = 'position: fixed; top: 70px; left: 50%; transform: translateX(-50%); background: #ff6b6b; color: white; padding: 15px 20px; border-radius: 5px; z-index: 10000; box-shadow: 0 4px 6px rgba(0,0,0,0.3); font-weight: bold; max-width: 600px; text-align: center;';
                                    warningDiv.textContent = warningMsg;
                                    document.body.appendChild(warningDiv);
                                    setTimeout(function() {
                                        if (warningDiv.parentNode) {
                                            warningDiv.parentNode.removeChild(warningDiv);
                                        }
                                    }, 5000);
                                }
                            }
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
                    // DO MUST BE ONE PAGE ONLY - remove any additional pages
                    if (renderedPages.length > 1) {
                        // Keep only the first page, remove all others
                        for (var i = 1; i < renderedPages.length; i++) {
                            renderedPages[i].parentNode.removeChild(renderedPages[i]);
                        }
                        renderedPages = [renderedPages[0]];
                    }
                    
                    if (renderedPages.length > 0) {
                        var totalPages = 1; // DO is always one page
                        renderedPages.forEach(function (page, index) {
                            page.classList.remove('print-page--last');
                            // Add page number attributes for display
                            page.setAttribute('data-page-number', 1);
                            page.setAttribute('data-total-pages', 1);
                        });
                        renderedPages[renderedPages.length - 1].classList.add('print-page--last');
                    }
                    
                    // Update vertical lines after pagination completes
                    // Force a layout recalculation by accessing offsetHeight to ensure accurate measurements
                    if (renderedPages.length > 0) {
                        renderedPages[0].offsetHeight; // Force layout recalculation
                    }
                    
                    if (window.updateVerticalLines) {
                        window.updateVerticalLines();
                        // Also update after delays to ensure layout is fully settled
                        setTimeout(window.updateVerticalLines, 100);
                        setTimeout(window.updateVerticalLines, 200);
                    }

                    if (renderedPages.length > 0) {
                        var totalPages = renderedPages.length;
                        renderedPages.forEach(function (page, index) {
                            // Add page number attributes for display
                            page.setAttribute('data-page-number', index + 1);
                            page.setAttribute('data-total-pages', totalPages);
                        });
                    }

                    // Update vertical lines after pagination completes and pages are marked
                    // Force a layout recalculation by accessing offsetHeight to ensure accurate measurements
                    if (renderedPages.length > 0) {
                        renderedPages[0].offsetHeight; // Force layout recalculation
                    }
                    
                    if (window.updateVerticalLines) {
                        window.updateVerticalLines();
                        // Also update after delays to ensure layout is fully settled
                        setTimeout(window.updateVerticalLines, 100);
                        setTimeout(window.updateVerticalLines, 200);
                    }

                    // Make pages-container visible and hide print-source
                    pagesContainer.style.visibility = '';
                    pagesContainer.style.position = '';
                    pagesContainer.style.left = '';
                    pagesContainer.style.right = '';
                    pagesContainer.style.top = '';
                    pagesContainer.style.display = 'flex'; // Ensure it's visible
                    pagesContainer.removeAttribute('data-measuring');
                    source.style.display = 'none';

                    // Update page counter
                    var pageCounter = document.getElementById('page-counter');
                    if (pageCounter) {
                        var pageCount = renderedPages.length;
                        if (pageCount > 0) {
                            pageCounter.textContent = 'Total Pages: ' + pageCount + (pageCount === 1 ? ' page' : ' pages');
                            pageCounter.classList.add('show');
                        } else {
                            pageCounter.classList.remove('show');
                        }
                    }
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
                            // Clear cache and force recalculation when entering print mode
                            pageHeightCache = null;
                            paginateDeliveryOrder(true);
                            // Recalculate vertical lines after print styles are applied
                            // Use multiple timeouts to ensure layout is fully settled
                            setTimeout(function() {
                                if (window.updateVerticalLines) {
                                    window.updateVerticalLines();
                                }
                            }, 100);
                            setTimeout(function() {
                                if (window.updateVerticalLines) {
                                    window.updateVerticalLines();
                                }
                            }, 200);
                        }
                    });
                } else if (mq.addListener) {
                    mq.addListener(function (e) {
                        if (e.matches) {
                            // Clear cache and force recalculation when entering print mode
                            pageHeightCache = null;
                            paginateDeliveryOrder(true);
                            // Recalculate vertical lines after print styles are applied
                            setTimeout(function() {
                                if (window.updateVerticalLines) {
                                    window.updateVerticalLines();
                                }
                            }, 100);
                            setTimeout(function() {
                                if (window.updateVerticalLines) {
                                    window.updateVerticalLines();
                                }
                            }, 200);
                        }
                    });
                }
            }

            window.addEventListener('beforeprint', function () {
                // Clear cache and force recalculation with print context
                // Use a small delay to ensure print media query is active
                pageHeightCache = null;
                setTimeout(function() {
                    paginateDeliveryOrder(true);
                    // Recalculate vertical lines after print layout is applied
                    // Need to wait for print styles to be fully applied and layout to settle
                    setTimeout(function() {
                        if (window.updateVerticalLines) {
                            window.updateVerticalLines();
                        }
                    }, 100);
                    setTimeout(function() {
                        if (window.updateVerticalLines) {
                            window.updateVerticalLines();
                        }
                    }, 200);
                }, 10);
            });
            
            // Also listen for afterprint to restore screen view
            window.addEventListener('afterprint', function () {
                // Recalculate for screen view after printing
                pageHeightCache = null;
                setTimeout(function() {
                    paginateDeliveryOrder(true);
                }, 10);
            });
        })();
    </script>
</body>
</html>
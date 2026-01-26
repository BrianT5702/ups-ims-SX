<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quotation Preview</title>
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
            font-family: Tahoma, Arial, sans-serif; /* Tahoma - thicker text, similar size to Arial, better letter "I" rendering */
        }
        
        body { 
            font-family: Tahoma, Arial, sans-serif; /* Use Tahoma - thicker text, similar size to Arial, better letter "I" rendering */
            color: #000; 
            background-color: #fff; 
            font-size: 14px; /* Smaller base font size */
            line-height: 1.3; /* Reduced line spacing */
            zoom: 1; /* Force 1:1 zoom */
            transform: scale(1); /* Additional normalization */
            transform-origin: top left;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .container { max-width: 1000px; /* Wider for letter size */ margin: 20px auto; border: 1px solid #000; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); background-color: #fff; min-height: 100vh; /* Full viewport height for screen view */ position: relative; display: flex; flex-direction: column; }
        .content { padding: 24px 20px 20px; flex: 0 0 auto; /* Don't grow */ }
        .company-info { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 6px; }
        .company-info-left { text-align: left; width: 70%; }
        .company-info-left h2 { font-size: calc(1.1em + 1px); /* Match DO font size */ margin-bottom: 6px; line-height: 1.2; }
        .company-info-right { text-align: right; margin-top: 0; width: 28%; min-width: 200px; }
        .company-info-right h2 { margin-bottom: 4px; white-space: nowrap; font-size: calc(1.0em + 1px); /* Match DO font size */ text-transform: uppercase; }
        .company-info h2 { margin-bottom: 4px; color: #000; font-weight: bold; font-size: calc(1.1em + 1px); /* Match DO font size */ white-space: nowrap; text-transform: uppercase; }
        .company-info p { margin: 0; font-size: calc(0.78em + 1px); /* Match DO font size */ line-height: 1.3; /* Reduced line spacing */ }
        .customer-info { display: flex; justify-content: space-between; margin-bottom: 14px; }
        .customer-info-frame { border: 1px solid #000; padding: 6px; width: 100%; font-size: 1.1em; /* Match DO font size */ line-height: 1.3; /* Reduced line spacing */ }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; font-size: 0.85em; /* Smaller font */ }
        .items-table th { padding: 6px 8px 4px 8px; /* Reduced padding */ text-align: left; border-bottom: 1px solid #000; border-top: 1px solid #000; font-weight: bold; text-transform: uppercase; font-size: 0.8em; /* Header font */ line-height: 1.3; /* Reduced line spacing */ }
        .items-table td { padding: 4px 8px; /* Reduced padding */ text-align: left; vertical-align: top; border-bottom: none; font-size: 1.1em; /* Smaller font */ line-height: 1.3; /* Reduced line spacing */ }
        .items-table th:nth-child(1), .items-table td:nth-child(1) { width: 5%; text-align: center; }
        .items-table th:nth-child(2), .items-table td:nth-child(2) { width: 60%; }
        .items-table th:nth-child(3), .items-table td:nth-child(3) { width: 8%; text-align: right; white-space: nowrap; }
        .items-table th:nth-child(4), .items-table td:nth-child(4) { width: 12%; text-align: right; white-space: nowrap; }
        .items-table th:nth-child(5), .items-table td:nth-child(5) { width: 15%; text-align: right; white-space: nowrap; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tfoot { border-top: 1px dotted #000; }
        .items-table tfoot .total-row:first-child td { padding-top: 8px; border-top: none; }
        .items-table tfoot .total-row:last-child td { border-top: 1px solid #000; }
        .total-row td { border-top: none; padding: 6px; /* Reduced padding */ }
        .total { font-weight: bold; text-transform: uppercase; }
        .total-row td:first-child { text-align: left !important; }
        .signature-section { display: flex !important; justify-content: space-between !important; align-items: flex-end !important; border-top: 1px solid #000 !important; padding: 10px 0 8px; /* Reduced padding */ margin-top: auto; page-break-inside: avoid; break-inside: avoid; page-break-after: avoid; page-break-before: avoid; font-size: 0.75em; /* Smaller font */ line-height: 1.3; /* Reduced line spacing */ flex: 0 0 auto; }
        .signature-section p, .signature-section strong { text-transform: uppercase; }
        .signature-left { width: 45% !important; }
        .signature-right { width: 45% !important; text-align: center !important; }
        .signature-line { border-bottom: 1px solid #000 !important; margin-top: 30px !important; /* Reduced margin */ margin-bottom: 3px !important; }
        .signature-label { font-size: 0.75em !important; /* Smaller font */ color: #000 !important; text-transform: uppercase; font-weight: bold !important; text-align: center !important; line-height: 1.3; /* Reduced line spacing */ }
        .button-container { text-align: right; padding: 20px; position: relative; }
        .print-button { position: fixed; top: 0; right: 0; padding: 10px 20px; font-size: 17px; color: #fff; background-color: #007bff; border: none; border-radius: 4px; cursor: pointer; z-index: 1000; }
        .back-button { position: fixed; top: 0; left: 0; padding: 10px 20px; font-size: 17px; color: #fff; background-color: #007bff; border: none; border-radius: 4px; cursor: pointer; z-index: 1000; }
        .print-button:hover, .back-button:hover { background-color: #0056b3; }

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

        @page { margin: 0.75cm; size: letter; /* Changed from A4 to letter size */ }
        @media print {
            /* Force standardized print settings */
            html {
                zoom: 1 !important;
                font-size: 14px !important; /* Smaller font */
            }
            
            body { 
                font-family: Tahoma, Arial, sans-serif !important; /* Tahoma for better print quality */
                background-color: #fff; 
                counter-reset: page;
                zoom: 1 !important;
                transform: scale(1) !important;
                font-size: 14px !important; /* Smaller font */
                line-height: 1.3 !important; /* Reduced line spacing */
            }
            
            .company-info h2, .company-info-right h2 { white-space: nowrap !important; font-size: 1.2em !important; /* Match DO print font size */ color: #000 !important; }
            .print-button, .back-button { display: none !important; }
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
            
            .signature-section { 
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-end !important;
                border-top: 1px solid #000 !important;
                padding: 16px 0 12px !important;
                margin: 0 !important;
                margin-top: auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                width: 100%;
                box-sizing: border-box;
                flex: 0 0 auto;
                position: relative !important;
                background: white;
            }
        
        /* Amount in words positioning - use normal flow to match screen preview */
        div[style*="font-style: italic"] {
            position: relative !important;
            background: white !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
            .items-table thead { display: table-header-group; }
            .items-table tr { page-break-inside: avoid; }
            /* Do NOT use footer-group to avoid repeating totals on every page */
            .items-table tfoot { display: table-row-group; }
            /* Ensure signature section prints and stays on last page bottom - use normal flow */
            .signature-section { 
                display: flex !important; 
                justify-content: space-between !important; 
                align-items: flex-end !important; 
                page-break-inside: avoid !important; 
                break-inside: avoid !important; 
                visibility: visible !important;
                position: relative !important;
            }
            
            /* Ensure paginated pages signature uses normal flow */
            .pages-container .print-page-footer .signature-section {
                position: relative !important;
            }
            
            /* Ensure amount, totals, and remark in paginated pages use normal flow */
            .pages-container [data-page-amount],
            .pages-container [data-page-total],
            .pages-container [data-page-remark] {
                position: relative !important;
            }

            /* Reduce spacing around remark in print */
            .pages-container .print-page-body [data-page-remark] {
                margin-top: -8px !important;
                margin-bottom: -8px !important;
            }
            /* Enforce column widths in print mode */
            .items-table { table-layout: fixed !important; }
            .items-table th:nth-child(1), .items-table td:nth-child(1) { width: 5% !important; min-width: 5% !important; max-width: 5% !important; }
            .items-table th:nth-child(2), .items-table td:nth-child(2) { width: 60% !important; min-width: 60% !important; max-width: 60% !important; word-wrap: break-word; overflow-wrap: break-word; }
            .items-table th:nth-child(3), .items-table td:nth-child(3) { width: 8% !important; min-width: 8% !important; max-width: 8% !important; white-space: nowrap !important; }
            .items-table th:nth-child(4), .items-table td:nth-child(4) { width: 12% !important; min-width: 12% !important; max-width: 12% !important; white-space: nowrap !important; }
            .items-table th:nth-child(5), .items-table td:nth-child(5) { width: 15% !important; min-width: 15% !important; max-width: 15% !important; white-space: nowrap !important; }
            .items-table th { 
                font-size: 0.8em !important; 
                padding: 10px 10px 6px 10px !important; 
                white-space: nowrap !important;
                overflow: visible !important;
                text-overflow: unset !important;
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
        }
        .items-table { font-size: 0.85em; /* Smaller font */ }
        .items-table th { 
            font-size: 0.8em; /* Header font */
            line-height: 1.3;
            white-space: nowrap;
            overflow: visible;
            text-overflow: unset;
        }
        .items-table td { font-size: 1.1em; /* Match DO font size */ line-height: 1.3; }

        /* Top-right details: make black and +1px font size */
        .company-info-right { color: #000; }
        .company-info-right h2 { color: #000; font-size: calc(1.0em + 1px); /* Match DO font size */ }
        .company-info-right p { color: #000; font-size: calc(0.78em + 1px); /* Match DO font size */ }
        .company-info-right strong { text-transform: uppercase; }
        @media print {
            .company-info-right h2 { color: #000 !important; font-size: 1.2em !important; /* Match DO print font size */ }
            .company-info-right p { color: #000 !important; }
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

        /* Page number indicator on each page */
        .print-page::after {
            content: 'Page ' attr(data-page-number) ' of ' attr(data-total-pages);
            position: absolute;
            bottom: 0.5cm;
            right: 0.75cm;
            font-size: 0.7em;
            font-family: Tahoma, Arial, sans-serif;
            color: #000;
        }

        @media print {
            .print-page::after {
                display: block;
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
            }
            
            /* Ensure signature stays with content on the same page */
            .print-page-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            .print-page-footer .signature-section {
                page-break-before: avoid !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            /* Ensure last page content stays together with signature */
            .print-page--last .print-page-body {
                page-break-after: avoid !important;
            }
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

        /* Reduce spacing around remark - negative margins to counteract flex gap */
        .print-page-body [data-page-remark],
        .pages-container .print-page-body [data-page-remark] {
            margin-top: -8px !important; /* Reduce top gap from 14px to 6px (14 - 8 = 6) */
            margin-bottom: -8px !important; /* Reduce bottom gap from 14px to 6px (14 - 8 = 6) */
        }

        .print-page-footer {
            margin-top: auto;
            padding-top: 18px;
            flex: 0 0 auto;
        }

        /* Ensure signature template is always hidden - it's only used for cloning */
        #signature-template {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="print-reminder">✓ Optimized for Letter Size (8.5" × 11") paper</div>
    <div id="zoom-warning">⚠️ Browser zoom is not 100%! Press Ctrl+0 (Cmd+0 on Mac) to reset zoom for accurate printing.</div>
    <div id="page-counter" class="page-counter">Calculating pages...</div>
    <div class="container">
        <button onclick="history.back()" class="back-button">Back</button>
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
                            <h2>Quotation</h2>
                            <p class="gap"><strong>Quotation No:</strong> <strong>{{ $quotation->quotation_num }}</strong></p>
                            <p class="gap"><strong>Date:</strong> {{ \Carbon\Carbon::parse($quotation->date)->format('d/m/Y') }}</p>
                            <p class="gap"><strong>Reference No:</strong> {{ $quotation->ref_num ?? '-' }}</p>
                            <p class="gap"><strong>Terms:</strong> {{ $quotation->customerSnapshot->term ?? $quotation->customer->term ?? 'N/A' }}</p>
                            <p class="gap"><strong>Salesman:</strong> {{ strtoupper($quotation->salesman->username ?? 'N/A') }}</p>
                        </div>
                    </div>

                    <div class="customer-info">
                        <div class="customer-info-frame">
                            <p><strong>{{ $quotation->customerSnapshot->cust_name ?? $quotation->customer->cust_name ?? 'N/A' }}</strong></p>
                            @if($quotation->customerSnapshot->address_line1 ?? $quotation->customer->address_line1)
                                <p>{{ $quotation->customerSnapshot->address_line1 ?? $quotation->customer->address_line1 }}</p>
                            @endif
                            @if($quotation->customerSnapshot->address_line2 ?? $quotation->customer->address_line2)
                                <p>{{ $quotation->customerSnapshot->address_line2 ?? $quotation->customer->address_line2 }}</p>
                            @endif
                            @if($quotation->customerSnapshot->address_line3 ?? $quotation->customer->address_line3)
                                <p>{{ $quotation->customerSnapshot->address_line3 ?? $quotation->customer->address_line3 }}</p>
                            @endif
                            @if($quotation->customerSnapshot->address_line4 ?? $quotation->customer->address_line4)
                                <p>{{ $quotation->customerSnapshot->address_line4 ?? $quotation->customer->address_line4 }}</p>
                            @endif
                            <p>Contact Number: {{ $quotation->customerSnapshot->phone_num ?? $quotation->customer->phone_num }}</p>
                            <p>@if($quotation->customerSnapshot->fax_num ?? $quotation->customer->fax_num)Fax: {{ $quotation->customerSnapshot->fax_num ?? $quotation->customer->fax_num }} | @endif @if($quotation->customerSnapshot->email ?? $quotation->customer->email)Email: {{ $quotation->customerSnapshot->email ?? $quotation->customer->email }}@endif</p>
                        </div>
                    </div>
                </div>

                @php
                    $currency = $quotation->customerSnapshot->currency ?? ($quotation->customer->currency ?? 'RM');
                @endphp

                <div class="table-area" id="items-table-source">
                    <table class="items-table" id="items-table">
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
                            @foreach ($quotation->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
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
                                <td>{{ $item->qty }}</td>
                                <td>{{ ($item->unit_price ?? 0) > 0 ? number_format($item->unit_price, 2) : '' }}</td>
                                <td>{{ ($item->amount ?? 0) > 0 ? number_format($item->amount, 2) : '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!empty($quotation->remark))
                    <div id="remark-source" style="margin: 0 0 0 5%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; position: relative; z-index: 5; background: white;">
                        <div style="font-size: 0.95em; font-family: Tahoma, Arial, sans-serif; line-height: 1.3; color: #000; display: flex;">
                            <span style="font-weight: bold; min-width: 60px; text-transform: uppercase;">Remark:&nbsp;&nbsp;&nbsp;</span>
                            <div style="flex: 1;">{!! nl2br(e($quotation->remark)) !!}</div>
                        </div>
                    </div>
                @endif

                <div id="totals-source" class="totals-section" style="border-top: 1px dotted #000; padding-top: 8px;">
                    <div class="total-row" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0 2px 0; font-weight: bold; font-size: 1.05em; text-transform: uppercase;">
                        <span class="total-label" style="text-align: left; width: 75%;">Total</span>
                        <span class="total-value" style="text-align: right; width: 25%; white-space: nowrap;">{{ $currency }} {{ number_format($quotation->total_amount ?? 0, 2) }}</span>
                    </div>
                </div>

                @php
                    try {
                        $numberToWords = function($number) use (&$numberToWords) {
                            if ($number == 0) return 'ZERO';
                            
                            $ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN', 
                                     'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 
                                     'EIGHTEEN', 'NINETEEN'];
                            $tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
                            
                            if ($number < 20) {
                                return $ones[$number];
                            } elseif ($number < 100) {
                                return $tens[intval($number / 10)] . ($number % 10 ? ' ' . $ones[$number % 10] : '');
                            } elseif ($number < 1000) {
                                return $ones[intval($number / 100)] . ' HUNDRED' . ($number % 100 ? ' ' . $numberToWords($number % 100) : '');
                            } elseif ($number < 1000000) {
                                return $numberToWords(intval($number / 1000)) . ' THOUSAND' . ($number % 1000 ? ' ' . $numberToWords($number % 1000) : '');
                            } elseif ($number < 1000000000) {
                                return $numberToWords(intval($number / 1000000)) . ' MILLION' . ($number % 1000000 ? ' ' . $numberToWords($number % 1000000) : '');
                            } else {
                                return $numberToWords(intval($number / 1000000000)) . ' BILLION' . ($number % 1000000000 ? ' ' . $numberToWords($number % 1000000000) : '');
                            }
                        };
                        
                        $totalAmount = $quotation->total_amount ?? 0;
                        $integerPart = intval($totalAmount);
                        $decimalPart = intval(round(($totalAmount - $integerPart) * 100));
                        
                        $amountInWords = $numberToWords($integerPart) ?? '';
                        if ($decimalPart > 0) {
                            // For RM use SEN, for other currencies use CENTS
                            $decimalWord = ($currency === 'RM') ? 'SEN' : 'CENTS';
                            $amountInWords .= ' AND ' . $numberToWords($decimalPart) . ' ' . $decimalWord;
                        }
                        $amountInWords = '* ' . $currency . ' : ' . trim($amountInWords) . ' ONLY';
                    } catch (\Exception $e) {
                        $amountInWords = '';
                    }
                @endphp

                @if(!empty($amountInWords))
                <div id="amount-source" style="padding: 2px 0 4px; font-style: italic; font-size: 0.86em; page-break-inside: avoid; break-inside: avoid;">
                    {{ $amountInWords }}
                </div>
                @endif
            </div>

            <div id="pages-container" class="pages-container"></div>
        </div>
        
        @php
            try {
                $numberToWords = function($number) use (&$numberToWords) {
                    if ($number == 0) return 'ZERO';
                    
                    $ones = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN', 
                             'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN', 'SEVENTEEN', 
                             'EIGHTEEN', 'NINETEEN'];
                    $tens = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];
                    
                    if ($number < 20) {
                        return $ones[$number];
                    } elseif ($number < 100) {
                        return $tens[intval($number / 10)] . ($number % 10 ? ' ' . $ones[$number % 10] : '');
                    } elseif ($number < 1000) {
                        return $ones[intval($number / 100)] . ' HUNDRED' . ($number % 100 ? ' ' . $numberToWords($number % 100) : '');
                    } elseif ($number < 1000000) {
                        return $numberToWords(intval($number / 1000)) . ' THOUSAND' . ($number % 1000 ? ' ' . $numberToWords($number % 1000) : '');
                    } elseif ($number < 1000000000) {
                        return $numberToWords(intval($number / 1000000)) . ' MILLION' . ($number % 1000000 ? ' ' . $numberToWords($number % 1000000) : '');
                    } else {
                        return $numberToWords(intval($number / 1000000000)) . ' BILLION' . ($number % 1000000000 ? ' ' . $numberToWords($number % 1000000000) : '');
                    }
                };
                
                $totalAmount = $quotation->total_amount ?? 0;
                $integerPart = intval($totalAmount);
                $decimalPart = intval(round(($totalAmount - $integerPart) * 100));
                
                $amountInWords = $numberToWords($integerPart) ?? '';
                if ($decimalPart > 0) {
                    // For RM use SEN, for other currencies use CENTS
                    $decimalWord = ($currency === 'RM') ? 'SEN' : 'CENTS';
                    $amountInWords .= ' AND ' . $numberToWords($decimalPart) . ' ' . $decimalWord;
                }
                $amountInWords = '* ' . $currency . ' : ' . trim($amountInWords) . ' ONLY';
            } catch (\Exception $e) {
                $amountInWords = '';
            }
        @endphp

        <div id="signature-template" class="signature-section" style="display: none;">
            <div class="signature-left">
                <p><strong>RECEIVED BY</strong></p>
                <br><br><br>
                <div class="signature-line"></div>
                <p class="signature-label">Company Chop & Signature</p>
            </div>
            <div class="signature-right">
                <p><strong>{{ $companyProfile->company_name }}</strong></p>
                <br><br><br>
                <div class="signature-line" style="display: flex; align-items: center; justify-content: center; padding: 8px 0;">
                    <p style="font-size: 0.7em; color: #000; text-align: center; margin: 0; font-style: italic;">Computer Generated No Signature is required.</p>
                </div>
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
            try { paginateQuotation(true); } catch (e) {}
            // Mark as printed before opening print dialog
            fetch('{{ route('quotations.mark-printed', $quotation->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function() {
                setTimeout(function(){ 
                    try { paginateQuotation(true); } catch (e) {}
                    setTimeout(function(){ window.print(); }, 150);
                }, 50);
            }).catch(function(error) {
                console.error('Failed to mark as printed:', error);
                setTimeout(function(){ 
                    try { paginateQuotation(true); } catch (e) {}
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

            function getPageHeight(force, isPrintContext) {
                // Force recalculation when in print mode or when forced
                var isPrintMode = isPrintContext || (window.matchMedia && window.matchMedia('print').matches);
                if (force || !pageHeightCache || isPrintMode) {
                    // Use a more accurate method that accounts for print DPI
                    // In print mode, browsers use different DPI, so we need to recalculate
                    var letterPx = measurePx('11in');
                    var marginPx = measurePx('0.75cm');
                    var calculatedHeight = Math.max(1, Math.round(letterPx - (marginPx * 2)));
                    
                    if (isPrintMode) {
                        // For print, use more conservative height to ensure content fits
                        // Account for print DPI differences and browser rendering variations
                        // Letter size: 11in = 279.4mm, margins: 0.75cm each = 1.5cm total
                        // Printable area: 279.4mm - 15mm = 264.4mm
                        // Convert to pixels at 96 DPI: (264.4 / 25.4) * 96 ≈ 998px
                        // But in print, browsers may use 300 DPI, so we need to be more conservative
                        // Use 7% reduction for print to ensure everything fits
                        pageHeightCache = Math.round(calculatedHeight * 0.93);
                    } else {
                        // For screen preview, use 5% reduction
                        pageHeightCache = Math.round(calculatedHeight * 0.95);
                    }
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
                    table: table,
                    tbody: tbody,
                    footer: footerWrapper
                };
            }

            function paginateQuotation(force) {
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
                    var totalsSource = document.getElementById('totals-source');
                    var amountSource = document.getElementById('amount-source');
                    // Check if we're in print context (either print media query or beforeprint event)
                    var isPrintMode = window.matchMedia && window.matchMedia('print').matches;
                    var pageHeight = getPageHeight(force, isPrintMode);
                    // Use a larger tolerance in print mode to account for DPI differences
                    var tolerance = isPrintMode ? 15 : 4;
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

                    rows.forEach(function (row) {
                        var clone = row.cloneNode(true);
                        ensurePage();
                        activePage.tbody.appendChild(clone);

                        if (activePage.page.offsetHeight > (usableHeight - tolerance)) {
                            activePage.tbody.removeChild(clone);
                            activePage = null;
                            ensurePage();
                            activePage.tbody.appendChild(clone);
                        }
                    });

                    if (rows.length === 0) {
                        ensurePage();
                    }

                    function appendBlock(sourceNode, attr) {
                        if (!sourceNode) {
                            return;
                        }
                        var clone = sourceNode.cloneNode(true);
                        removeIds(clone);
                        if (attr) {
                            clone.setAttribute(attr, '');
                        }
                        ensurePage();
                        activePage.body.appendChild(clone);
                        if (attr === 'data-page-total') {
                            clone.style.marginTop = 'auto';
                        } else if (attr === 'data-page-amount') {
                            clone.style.marginTop = '2px';
                        }
                        // Check if page overflows, accounting for signature footer
                        var pageHeight = activePage.page.offsetHeight;
                        if (pageHeight > (usableHeight - tolerance)) {
                            // If this is the amount (last element), allow slight overflow to keep signature together
                            var isLastElement = (attr === 'data-page-amount');
                            if (isLastElement && pageHeight <= (usableHeight + 30)) {
                                // Allow slight overflow on last page to keep signature together
                                // This prevents signature from being pushed to a new page
                                return;
                            }
                            
                            activePage.body.removeChild(clone);
                            activePage = null;
                            ensurePage();
                            activePage.body.appendChild(clone);
                            if (attr === 'data-page-total') {
                                clone.style.marginTop = 'auto';
                            } else if (attr === 'data-page-amount') {
                                clone.style.marginTop = '2px';
                            }
                        }
                    }

                    appendBlock(remarkSource, 'data-page-remark');
                    appendBlock(totalsSource, 'data-page-total');
                    appendBlock(amountSource, 'data-page-amount');

                    var renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                    renderedPages.forEach(function (page) {
                        var tbody = page.querySelector('tbody');
                        var hasRows = tbody && tbody.children.length > 0;
                        var hasExtras = page.querySelector('[data-page-remark], [data-page-total], [data-page-amount]');
                        if (!hasRows && !hasExtras) {
                            page.parentNode.removeChild(page);
                        }
                    });

                    renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                    if (renderedPages.length > 0) {
                        var totalPages = renderedPages.length;
                        renderedPages.forEach(function (page, index) {
                            page.classList.remove('print-page--last');
                            // Add page number attributes for display
                            page.setAttribute('data-page-number', index + 1);
                            page.setAttribute('data-total-pages', totalPages);
                        });
                        renderedPages[renderedPages.length - 1].classList.add('print-page--last');
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
                    paginateQuotation(false);
                });
            }

            window.paginateQuotation = paginateQuotation;

            window.addEventListener('load', function () {
                paginateQuotation(true);
            });

            window.addEventListener('resize', schedulePaginate);

            if (window.matchMedia) {
                var mq = window.matchMedia('print');
                if (mq.addEventListener) {
                    mq.addEventListener('change', function (e) {
                        if (e.matches) {
                            // Clear cache and force recalculation when entering print mode
                            pageHeightCache = null;
                            paginateQuotation(true);
                        }
                    });
                } else if (mq.addListener) {
                    mq.addListener(function (e) {
                        if (e.matches) {
                            // Clear cache and force recalculation when entering print mode
                            pageHeightCache = null;
                            paginateQuotation(true);
                        }
                    });
                }
            }

            window.addEventListener('beforeprint', function () {
                // Clear cache and force recalculation with print context
                // Use a small delay to ensure print media query is active
                pageHeightCache = null;
                setTimeout(function() {
                    paginateQuotation(true);
                }, 10);
            });
            
            // Also listen for afterprint to restore screen view
            window.addEventListener('afterprint', function () {
                // Recalculate for screen view after printing
                pageHeightCache = null;
                setTimeout(function() {
                    paginateQuotation(true);
                }, 10);
            });
        })();
    </script>
</body>
</html>


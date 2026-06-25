<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quotation Preview</title>
    @include('partials.preview-document-header-styles')
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
            font-family: Tahoma, Arial, sans-serif;
            color: #000; 
            background-color: #fff; 
            min-height: 100vh;
            font-size: 15px;
            line-height: 1.45;
            zoom: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .container { max-width: 1000px; margin: 20px auto; border: 1px solid #000; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); background-color: #fff; min-height: 100vh; position: relative; display: flex; flex-direction: column; }
        .table-area { position: relative; display: flex; flex-direction: column; }
        .print-page .table-area { flex: 0 0 auto; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 0; padding-bottom: 0; table-layout: fixed; font-size: 0.9em; position: relative; }
        .items-table th { padding: 4px 8px; text-align: left; border-bottom: 1px solid #000; border-top: 1px solid #000; font-weight: bold; text-transform: uppercase; font-size: 0.75em; line-height: 1.3; vertical-align: middle; }
        .items-table td { padding: 3.7px 8px; text-align: left; vertical-align: top; border-bottom: none; font-size: 1.06em; line-height: 1.15; word-wrap: break-word; word-break: break-word; }
        .items-table td .item-detail-lines { padding-left: 0 !important; margin-top: 5px; font-size: 1em; color: #000; }
        .items-table td .quotation-more-description { padding-left: 15px; font-size: 1.0em; color: #000; margin-top: 0; margin-bottom: 0; }
        .items-table tr.quotation-continuation-row td { line-height: 1.15; }
        .items-table th:nth-child(1), .items-table td:nth-child(1) { width: 5%; text-align: center; }
        .items-table th:nth-child(2), .items-table td:nth-child(2) { width: 60%; }
        .items-table th:nth-child(3), .items-table td:nth-child(3) { width: 8%; text-align: right; white-space: nowrap; }
        .items-table th:nth-child(4), .items-table td:nth-child(4) { width: 12%; text-align: right; white-space: nowrap; }
        .items-table th:nth-child(5), .items-table td:nth-child(5) { width: 15%; text-align: right; white-space: nowrap; }
        .items-table td:nth-child(1), .items-table td:nth-child(3) { font-size: calc(1em - 0.5px); }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tfoot { border-top: 1px dotted #000; }
        .items-table tfoot .total-row:first-child td { padding-top: 8px; border-top: none; }
        .items-table tfoot .total-row:last-child td { border-top: 1px solid #000; }
        .total-row td { border-top: none; padding: 6px; /* Reduced padding */ }
        .total { font-weight: bold; text-transform: uppercase; }
        .total-row td:first-child { text-align: left !important; }

        .quotation-totals-footer {
            border-top: 1px dotted #000;
            padding-top: 4px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .quotation-totals-footer .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0 4px;
            margin: 0;
            font-weight: bold;
            font-size: 1em;
            text-transform: uppercase;
        }

        .quotation-totals-footer .total-label {
            text-align: left;
            width: 75%;
        }

        .quotation-totals-footer .total-value {
            text-align: right;
            width: 25%;
            white-space: nowrap;
        }

        .quotation-totals-footer .amount-in-words {
            margin: 6px 0 0;
            padding: 0;
            font-style: italic;
            font-size: 0.9em;
            line-height: 1.3;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .print-page-body [data-page-totals-footer] {
            margin-top: 0;
        }

        .print-page-bottom {
            margin-top: auto;
            flex: 0 0 auto;
        }

        .print-page-bottom .print-page-footer {
            margin-top: 14px;
            padding-top: 0;
        }

        .signature-section {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            border-top: 1px solid #000 !important;
            padding: 0 0 8px !important;
            margin-top: 0;
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: avoid;
            page-break-before: avoid;
            font-size: 13px;
            line-height: 1.2;
            width: 100%;
            flex: 0 0 auto;
            position: relative;
        }
        .signature-section p, .signature-section strong { text-transform: uppercase; margin: 0; padding: 0; line-height: 1.2; }
        .signature-left, .signature-right {
            width: 48% !important;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }
        .signature-right { text-align: center !important; }
        .signature-title {
            margin: 0;
            min-height: 2.4em;
            line-height: 1.2;
        }
        .signature-gap {
            min-height: 54px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .signature-right .signature-gap .signature-disclaimer {
            margin-bottom: 12px;
        }
        .signature-disclaimer {
            margin: 0;
            font-size: 0.85em;
            font-style: italic;
            text-align: center;
            text-transform: none;
        }
        .signature-line { border-bottom: 1px solid #000 !important; margin-top: 0 !important; margin-bottom: 4px !important; }
        .signature-label { font-size: 0.9em !important; color: #000 !important; text-transform: uppercase; font-weight: bold !important; text-align: center !important; line-height: 1.2; }
        .signature-label-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            width: 100%;
            gap: 8px;
        }
        .signature-label-row .signature-label {
            flex: 1 1 auto;
            text-align: center !important;
        }
        .print-page-number {
            flex: 0 0 auto;
            font-size: 0.7em;
            font-family: Tahoma, Arial, sans-serif;
            color: #000;
            line-height: 1.2;
            text-transform: none;
            font-weight: normal;
            white-space: nowrap;
            pointer-events: none;
        }
        .button-container { text-align: right; padding: 20px; position: relative; }
        .preview-actions {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 10001;
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: fit-content;
            min-width: 120px;
            padding: 6px;
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
        }
        .back-button, .print-button { width: 100%; padding: 10px 20px; font-size: 17px; color: #fff; border: none; border-radius: 4px; cursor: pointer; box-sizing: border-box; text-align: center; }
        .print-button { background-color: #007bff; }
        .back-button { background-color: #6c757d; }
        .print-button:hover { background-color: #0056b3; }
        .back-button:hover { background-color: #5a6268; }

        /* On-screen reminder for correct print formatting */
        .print-reminder {
            position: fixed;
            top: 132px;
            right: 20px;
            padding: 4px 8px;
            font-size: 10px;
            line-height: 1.2;
            color: #856404;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            z-index: 1000;
            max-width: 150px;
        }

        /* Force standard zoom detection warning */
        #zoom-warning {
            position: fixed;
            top: 172px;
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

        @page { margin: 0.75cm 0.75cm 0.15cm 0.75cm; size: letter; }
        @media print {
            html {
                zoom: 1 !important;
                font-size: 16px !important;
            }
            
            body { 
                font-family: Tahoma, Arial, sans-serif !important;
                background-color: #fff; 
                counter-reset: page;
                zoom: 1 !important;
                transform: scale(1) !important;
                font-size: 15px !important;
                line-height: 1.45 !important;
            }
            
            .preview-actions { display: none !important; }
            html, body {
                height: auto;
                margin: 0;
                padding: 0;
            }
            
            @page {
                margin: 0.75cm 0.75cm 0.15cm 0.75cm;
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
                padding: 20px 20px 4px 20px !important;
                box-sizing: border-box !important;
                min-height: calc(11in - 0.75cm - 0.15cm) !important;
                overflow: hidden !important;
            }
            
            .signature-section { 
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                border-top: 1px solid #000 !important;
                padding: 0 0 8px !important;
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
                font-size: 13px !important;
            }
        
            .items-table { font-size: 0.9em !important; table-layout: fixed !important; }
            .items-table th {
                padding: 4px 8px !important;
                font-size: 0.75em !important;
                line-height: 1.3 !important;
                white-space: nowrap !important;
                overflow: visible !important;
                text-overflow: unset !important;
            }
            .items-table td {
                padding: 3.7px 8px !important;
                font-size: 1.06em !important;
                line-height: 1.14 !important;
            }
            .items-table td:nth-child(1),
            .items-table td:nth-child(3) {
                font-size: calc(1em - 0.5px) !important;
            }
            .items-table th:nth-child(1), .items-table td:nth-child(1) { width: 5% !important; min-width: 5% !important; max-width: 5% !important; }
            .items-table th:nth-child(2), .items-table td:nth-child(2) { width: 60% !important; min-width: 60% !important; max-width: 60% !important; word-wrap: break-word; overflow-wrap: break-word; }
            .items-table th:nth-child(3), .items-table td:nth-child(3) { width: 8% !important; min-width: 8% !important; max-width: 8% !important; white-space: nowrap !important; }
            .items-table th:nth-child(4), .items-table td:nth-child(4) { width: 12% !important; min-width: 12% !important; max-width: 12% !important; white-space: nowrap !important; }
            .items-table th:nth-child(5), .items-table td:nth-child(5) { width: 15% !important; min-width: 15% !important; max-width: 15% !important; white-space: nowrap !important; }

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
            .pages-container .print-page-footer .signature-section {
                position: relative !important;
                padding: 0 0 8px !important;
            }
            
            /* Ensure amount, totals, and remark in paginated pages use normal flow */
            .pages-container [data-page-amount],
            .pages-container [data-page-total],
            .pages-container [data-page-totals-footer],
            .pages-container [data-page-remark] {
                position: relative !important;
            }

            #remark-wrapper {
                page-break-inside: avoid;
                break-inside: avoid;
                position: relative;
                z-index: 5;
                background: white;
            }

            .pages-container .print-page-body [data-page-remark] {
                margin-top: -3px !important;
                margin-bottom: -3px !important;
            }

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
                padding: 20px 20px 4px 20px !important;
                position: relative !important;
            }
            
            /* Ensure print-page-body and print-page-footer use normal flow */
            .pages-container .print-page-body,
            .pages-container .print-page-footer {
                position: relative !important;
            }
        }
        .items-table th { 
            white-space: nowrap;
            overflow: visible;
            text-overflow: unset;
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

        /* Page counter display - hidden to match DO */
        .page-counter {
            display: none !important;
        }

        .page-counter.show {
            display: none !important;
        }

        /* Make pages-container pages match container styling on screen for accurate preview */
        .pages-container .print-page {
            background-color: #fff;
            border: 1px solid #000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto 28px;
            max-width: 1000px;
            padding: 20px 20px 4px 20px;
            box-sizing: border-box;
        }

        .pages-container .print-page:last-child {
            margin-bottom: 0;
        }

        /* Make signature padding in pages-container match print (no horizontal padding since page has padding) */
        .pages-container .print-page-footer .signature-section {
            padding: 0 0 8px !important;
        }

        .pages-container .print-page-bottom .print-page-footer {
            margin-top: 14px;
        }

        .pages-container[data-measuring="true"] .print-page {
            min-height: auto !important;
        }

        .print-page {
            display: flex;
            flex-direction: column;
            position: relative;
            min-height: calc(11in - 0.75cm - 0.15cm);
            page-break-after: always;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Last page shouldn't force a page break after */
        .print-page--last {
            page-break-after: auto;
        }

        @media print {
            .print-page--last {
                page-break-after: auto;
            }

            .print-page-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                orphans: 3;
                widows: 3;
            }

            .print-page-footer .signature-section {
                page-break-before: avoid !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .print-page-number {
                display: block !important;
                visibility: visible !important;
            }

            .print-page--last .print-page-body {
                page-break-after: avoid !important;
            }
        }

        .print-page-body {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1 1 auto;
        }

        /* Reduce spacing around remark - negative margins to counteract flex gap */
        .print-page-body [data-page-remark],
        .pages-container .print-page-body [data-page-remark] {
            margin-top: -3px !important;
            margin-bottom: -3px !important;
        }

        .print-page-footer {
            margin-top: auto;
            padding-top: 0;
            flex: 0 0 auto;
        }

        /* TOTAL only on the last page; signature appears on every page */
        .print-page:not(.print-page--last) [data-page-totals-footer] {
            display: none !important;
        }

        /* Ensure signature template is always hidden - it's only used for cloning */
        #signature-template {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="preview-actions">
        <button type="button" onclick="triggerPrint()" class="print-button">Print</button>
        <button type="button" onclick="history.back()" class="back-button">Back</button>
    </div>
    <div class="print-reminder">✓ Optimized for Letter Size (8.5" × 11") paper</div>
    <div id="zoom-warning">⚠️ Browser zoom is not 100%! Press Ctrl+0 (Cmd+0 on Mac) to reset zoom for accurate printing.</div>
    <div id="page-counter" class="page-counter">Calculating pages...</div>
    <div class="container">
        <div class="content">
            <div id="print-source">
                <div class="page-header">
                    <div class="company-info">
                        <div class="company-info-left">
                            <div class="company-logo-wrap">
                                <img src="{{ asset('images/company-logo-1.png') }}" alt="{{ $companyProfile->company_name ?? 'Company' }}" />
                            </div>
                            <div class="company-info-text">
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
                        </div>
                        <div class="company-info-right">
                            <h2>Quotation</h2>
                            <p><strong>Quotation No:</strong> <strong>{{ $quotation->quotation_num }}</strong></p>
                            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($quotation->date)->format('d/m/Y') }}</p>
                            <p><strong>Reference No:</strong> {{ $quotation->ref_num ?? '-' }}</p>
                            <p><strong>Terms:</strong> {{ $quotation->customerSnapshot->term ?? $quotation->customer->term ?? 'N/A' }}</p>
                            <p><strong>Salesman:</strong> {{ strtoupper($quotation->salesman->username ?? 'N/A') }}</p>
                        </div>
                    </div>

                    <div class="customer-info">
                        <div class="customer-info-frame">
                            <p><strong>To: </strong><strong>{{ $quotation->customerSnapshot->cust_name ?? $quotation->customer->cust_name ?? 'N/A' }}</strong></p>
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
                            @php
                                use App\Support\QuotationPrintLayout;

                                $printLayout = QuotationPrintLayout::fromQuotationItems($quotation->items);
                                $layoutBaseRows = $printLayout->baseRowMap();
                                $itemsById = $quotation->items->keyBy('id');
                                $rowsToShow = $printLayout->previewRowsToShow();

                                $rowSequenceMap = [];
                                $occupiedRows = [];
                                foreach ($layoutBaseRows as $rowIndex => $itemId) {
                                    $seqItem = $itemsById->get($itemId);
                                    if (! $seqItem) {
                                        continue;
                                    }
                                    $hasSequenceContent = $seqItem->item_id !== null
                                        || trim((string) ($seqItem->custom_item_name ?? '')) !== '';
                                    $sequenceHidden = (bool) ($seqItem->sequence_hidden ?? false);
                                    if ($hasSequenceContent && ! $sequenceHidden) {
                                        $occupiedRows[] = (int) $rowIndex;
                                    }
                                }
                                sort($occupiedRows, SORT_NUMERIC);
                                foreach ($occupiedRows as $i => $occupiedRow) {
                                    $rowSequenceMap[$occupiedRow] = $i + 1;
                                }
                            @endphp
                            @for($rowIndex = 0; $rowIndex < $rowsToShow; $rowIndex++)
                                @php
                                    $continuation = $printLayout->continuationAt($rowIndex);
                                    $itemId = $layoutBaseRows[$rowIndex] ?? null;
                                    $item = $itemId ? $itemsById->get($itemId) : null;
                                    $isContinuationRow = $continuation !== null;
                                @endphp
                                <tr class="{{ $isContinuationRow ? 'quotation-continuation-row' : '' }}">
                                    @if($item)
                                        <td>
                                            @if(isset($rowSequenceMap[$rowIndex]))
                                                {{ $rowSequenceMap[$rowIndex] }}
                                            @else
                                                &nbsp;
                                            @endif
                                        </td>
                                        <td>{{ $item->custom_item_name ?? ($item->item->item_name ?? 'N/A') }}</td>
                                        <td>
                                            @if($item->item_id === null)
                                                @if($item->qty > 0)
                                                    @php
                                                        $q = floatval($item->qty);
                                                        $qtyFmt = (round($q) == $q) ? number_format($q, 0) : ((round($q * 100) == round($q * 10) * 10) ? number_format($q, 1) : number_format($q, 2));
                                                    @endphp
                                                    {{ $qtyFmt }}{{ !empty($item->custom_um) ? ' ' . $item->custom_um : '' }}
                                                @else
                                                    &nbsp;
                                                @endif
                                            @else
                                                @php
                                                    $unit = !empty(trim($item->custom_um ?? '')) ? trim($item->custom_um) : ($item->item->um ?? 'UNITS');
                                                    $unit = ($unit === 'UNIT') ? 'UNITS' : $unit;
                                                    $q = floatval($item->qty);
                                                    $qtyFmt = (round($q) == $q) ? number_format($q, 0) : ((round($q * 100) == round($q * 10) * 10) ? number_format($q, 1) : number_format($q, 2));
                                                @endphp
                                                {{ $qtyFmt }} {{ $unit }}
                                            @endif
                                        </td>
                                        <td>{{ ($item->unit_price ?? 0) > 0 ? number_format($item->unit_price, 2) : '' }}</td>
                                        <td>{{ ($item->amount ?? 0) > 0 ? number_format($item->amount, 2) : '' }}</td>
                                    @elseif($isContinuationRow)
                                        <td>&nbsp;</td>
                                        <td>
                                            @if($continuation['kind'] === 'desc_line')
                                                <div class="quotation-more-description" style="margin-top: 0; padding-left: 15px;">
                                                    • {{ $continuation['text'] }}
                                                </div>
                                            @else
                                                &nbsp;
                                            @endif
                                        </td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    @else
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    @endif
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                @if(!empty($quotation->remark))
                    <div id="remark-source">
                        <div style="margin: 0;">
                            <div id="remark-wrapper" style="padding-left: 8px; padding-top: 10px;">
                                <div style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <div style="font-size: 0.95em; font-family: Tahoma, Arial, sans-serif; line-height: 1.3; color: #000; display: flex;">
                                        <span style="font-weight: bold; min-width: 60px; text-transform: uppercase;">Remark:&nbsp;&nbsp;&nbsp;</span>
                                        <div style="flex: 1;">{!! nl2br(e($quotation->remark)) !!}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="totals-footer-source" class="quotation-totals-footer">
                    <div id="totals-source" class="totals-section">
                        <div class="total-row">
                            <span class="total-label">Total</span>
                            <span class="total-value">{{ $currency }} {{ number_format($quotation->total_amount ?? 0, 2) }}</span>
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
                <div id="amount-source" class="amount-in-words">
                    {{ $amountInWords }}
                </div>
                @endif
                </div>
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
                <p class="signature-title"><strong>RECEIVED BY</strong></p>
                <div class="signature-gap"></div>
                <div class="signature-line"></div>
                <p class="signature-label">Company Chop & Signature</p>
            </div>
            <div class="signature-right">
                <p class="signature-title"><strong>{{ $companyProfile->company_name }}</strong></p>
                <div class="signature-gap">
                    <p class="signature-disclaimer">Computer Generated No Signature is required.</p>
                </div>
                <div class="signature-line"></div>
                <p class="signature-label">(Authorized Signature)</p>
            </div>
        </div>
    </div>

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
            try { paginateQuotation(true, true); } catch (e) {}
            // Mark as printed before opening print dialog
            fetch('{{ route('quotations.mark-printed', ['id' => $quotation->id, 'db' => $connection ?? session('active_db')]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function() {
                setTimeout(function(){
                    setTimeout(function(){ window.print(); }, 100);
                }, 50);
            }).catch(function(error) {
                console.error('Failed to mark as printed:', error);
                setTimeout(function(){
                    setTimeout(function(){ window.print(); }, 100);
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
                var isPrintMode = isPrintContext || (window.matchMedia && window.matchMedia('print').matches);
                if (force || !pageHeightCache || isPrintMode) {
                    var letterPx = measurePx('11in');
                    var topMarginPx = measurePx('0.75cm');
                    var bottomMarginPx = measurePx('0.15cm');
                    var calculatedHeight = Math.max(1, Math.round(letterPx - topMarginPx - bottomMarginPx));

                    if (isPrintMode) {
                        pageHeightCache = calculatedHeight;
                    } else {
                        pageHeightCache = Math.round(calculatedHeight * 0.999);
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

            function measurePrintPagePadding() {
                if (measurePrintPagePadding.cache) {
                    return measurePrintPagePadding.cache;
                }
                var container = document.createElement('div');
                container.className = 'pages-container';
                container.setAttribute('data-measuring', 'true');
                container.style.cssText = 'position:absolute;visibility:hidden;left:-9999px;width:1000px;';
                var probe = document.createElement('div');
                probe.className = 'print-page';
                container.appendChild(probe);
                document.body.appendChild(container);
                var style = window.getComputedStyle(probe);
                var padding = parseFloat(style.paddingTop || 0) + parseFloat(style.paddingBottom || 0);
                document.body.removeChild(container);
                measurePrintPagePadding.cache = padding;
                return padding;
            }

            function measurePrintPageBottomHeight(totalsFooterSource, signatureTemplate) {
                var probe = document.createElement('div');
                probe.className = 'print-page-bottom';
                probe.style.cssText = 'position:absolute;visibility:hidden;left:-9999px;width:1000px;';
                if (totalsFooterSource) {
                    var totals = totalsFooterSource.cloneNode(true);
                    removeIds(totals);
                    totals.style.display = '';
                    probe.appendChild(totals);
                }
                var footerWrapper = document.createElement('div');
                footerWrapper.className = 'print-page-footer';
                var signature = signatureTemplate.cloneNode(true);
                removeIds(signature);
                signature.style.display = '';
                footerWrapper.appendChild(signature);
                probe.appendChild(footerWrapper);
                document.body.appendChild(probe);
                var height = probe.offsetHeight;
                document.body.removeChild(probe);
                return height;
            }

            function measureNodeHeight(node, wrapperClass) {
                if (!node) {
                    return 0;
                }
                var probe = document.createElement('div');
                probe.style.position = 'absolute';
                probe.style.visibility = 'hidden';
                probe.style.left = '-9999px';
                probe.style.width = '1000px';
                if (wrapperClass) {
                    probe.className = wrapperClass;
                }
                var clone = node.cloneNode(true);
                removeIds(clone);
                clone.style.display = '';
                probe.appendChild(clone);
                document.body.appendChild(probe);
                var height = probe.offsetHeight;
                document.body.removeChild(probe);
                return height;
            }

            function measureFooterHeight(signatureTemplate) {
                return measureNodeHeight(signatureTemplate, 'print-page-footer');
            }

            function measureTrailingSectionHeights(remarkSource, totalsFooterSource, signatureTemplate) {
                var remarkHeight = measureNodeHeight(remarkSource);
                var bottomHeight = measurePrintPageBottomHeight(totalsFooterSource, signatureTemplate);
                var totalsHeight = measureNodeHeight(totalsFooterSource);
                var signatureHeight = measureFooterHeight(signatureTemplate);
                // Flex gap between body children (table area → remark → bottom block).
                var bodyGapAllowance = remarkSource ? 10 : 5;
                return {
                    remarkHeight: remarkHeight,
                    totalsHeight: totalsHeight,
                    signatureHeight: signatureHeight,
                    bottomHeight: bottomHeight,
                    total: remarkHeight + bottomHeight + bodyGapAllowance
                };
            }

            function logLayoutDiagnostics(renderedPages, metrics) {
                var lines = [
                    'Letter printable height: ' + metrics.pageHeight + 'px',
                    'Usable page height (offsetHeight budget): ' + metrics.usableHeight + 'px',
                    'Max rows with TOTAL on page 1: ' + metrics.maxRowsWithFooter,
                    'Max items on page 1 when TOTAL is on last page: ' + metrics.firstPageItemsOnlyMax,
                    'Signature: every page | TOTAL: last page only',
                    'Header block (company + customer): ~' + metrics.headerHeight + 'px',
                    'Table header row: ~' + metrics.theadHeight + 'px',
                    'Each item row (incl. empty): ~' + metrics.rowHeight + 'px',
                    'Remark block: ' + metrics.trailing.remarkHeight + 'px',
                    'TOTAL section: ' + metrics.trailing.totalsHeight + 'px',
                    'SIGNATURE section: ' + metrics.trailing.signatureHeight + 'px',
                    'Reserved trailing (remark + totals + signature + gaps): ' + metrics.trailing.total + 'px',
                    'Estimated rows that fit on one page with footer: ~' + metrics.estimatedRowsOnOnePage
                ];
                renderedPages.forEach(function (page, index) {
                    var tbody = page.querySelector('tbody');
                    var rowCount = tbody ? tbody.children.length : 0;
                    var footer = page.querySelector('.print-page-footer');
                    lines.push(
                        'Page ' + (index + 1) + ': ' + page.offsetHeight + 'px total'
                        + ' (' + rowCount + ' table rows'
                        + (footer ? ', has signature' : '')
                        + (page.querySelector('[data-page-totals-footer]') ? ', has TOTAL' : '')
                        + ')'
                    );
                });
                console.group('Quotation preview layout');
                lines.forEach(function (line) { console.log(line); });
                console.groupEnd();
                window.__quotationLayoutDiagnostics = {
                    lines: lines,
                    metrics: metrics
                };
            }

            function renderLayoutDiagnosticsPanel(lines) {
                var panel = document.getElementById('layout-diagnostics');
                if (!panel) {
                    panel = document.createElement('div');
                    panel.id = 'layout-diagnostics';
                    panel.style.cssText = 'position:fixed;bottom:12px;left:12px;max-width:420px;max-height:45vh;overflow:auto;z-index:10001;background:#f8f9fa;border:1px solid #333;padding:10px 12px;font:12px/1.35 Tahoma,Arial,sans-serif;color:#000;box-shadow:0 2px 8px rgba(0,0,0,.15);';
                    document.body.appendChild(panel);
                }
                panel.innerHTML = '<strong>Layout diagnostics</strong><br>' + lines.map(function (line) {
                    return line.replace(/</g, '&lt;');
                }).join('<br>');
            }

            function ensurePageNumberElement(signatureSection) {
                if (!signatureSection) {
                    return null;
                }

                var existing = signatureSection.querySelector('.print-page-number');
                var signatureLabel = signatureSection.querySelector('.signature-right .signature-label');
                var labelRow = signatureSection.querySelector('.signature-label-row');

                if (existing && labelRow && labelRow.contains(existing)) {
                    return existing;
                }

                if (!existing) {
                    existing = document.createElement('div');
                    existing.className = 'print-page-number';
                    existing.setAttribute('aria-hidden', 'true');
                }

                if (signatureLabel && signatureLabel.parentNode) {
                    if (!labelRow) {
                        labelRow = document.createElement('div');
                        labelRow.className = 'signature-label-row';
                        signatureLabel.parentNode.insertBefore(labelRow, signatureLabel);
                        labelRow.appendChild(signatureLabel);
                    }
                    labelRow.appendChild(existing);

                    return existing;
                }

                signatureSection.appendChild(existing);

                return existing;
            }

            function appendSignatureFooter(page, signatureTemplate) {
                if (!page || !signatureTemplate) {
                    return;
                }
                var bottom = page.querySelector('.print-page-bottom');
                if (!bottom) {
                    bottom = document.createElement('div');
                    bottom.className = 'print-page-bottom';
                    page.appendChild(bottom);
                }
                if (bottom.querySelector('.print-page-footer')) {
                    ensurePageNumberElement(bottom.querySelector('.signature-section'));

                    return;
                }
                var footerWrapper = document.createElement('div');
                footerWrapper.className = 'print-page-footer';
                var signature = signatureTemplate.cloneNode(true);
                signature.style.display = '';
                removeIds(signature);
                ensurePageNumberElement(signature);
                footerWrapper.appendChild(signature);
                bottom.appendChild(footerWrapper);
            }

            function applyPageNumberLabels(pages) {
                pages.forEach(function (page, index) {
                    var number = index + 1;
                    var total = pages.length;
                    page.classList.remove('print-page--last');
                    page.setAttribute('data-page-number', number);
                    page.setAttribute('data-total-pages', total);
                    var label = ensurePageNumberElement(page.querySelector('.signature-section'));
                    if (label) {
                        label.textContent = 'Page ' + number + ' of ' + total;
                    }
                });

                if (pages.length > 0) {
                    pages[pages.length - 1].classList.add('print-page--last');
                }
            }

            function ensurePrintPageBottom(page) {
                var bottom = page.querySelector('.print-page-bottom');
                if (!bottom) {
                    bottom = document.createElement('div');
                    bottom.className = 'print-page-bottom';
                    page.appendChild(bottom);
                }
                return bottom;
            }

            function pageContentHeight(page) {
                return page ? page.offsetHeight : 0;
            }

            function tbodyRowCount(page) {
                if (!page) {
                    return 0;
                }
                var tbody = page.querySelector('tbody');
                return tbody ? tbody.children.length : 0;
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

                pagesContainer.appendChild(page);
                appendSignatureFooter(page, signatureTemplate);

                return {
                    page: page,
                    body: body,
                    table: table,
                    tbody: tbody
                };
            }

            function paginateQuotation(force, forcePrintMode) {
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
                    var totalsFooterSource = document.getElementById('totals-footer-source');
                    var isPrintLayout = !!(forcePrintMode || (window.matchMedia && window.matchMedia('print').matches));
                    var pageHeight = getPageHeight(force || forcePrintMode, isPrintLayout);
                    var tolerance = isPrintLayout ? 100 : 80;
                    var usableHeight = pageHeight;
                    var trailing = measureTrailingSectionHeights(remarkSource, totalsFooterSource, signatureTemplate);
                    var trailingReserve = trailing.total;
                    // ≤22 item lines: items + TOTAL + SIGNATURE on page 1.
                    var MAX_ROWS_WITH_FOOTER = 22;
                    // >22 item lines: page 1 = up to 25 items + SIGNATURE (no TOTAL); TOTAL on last page only.
                    var FIRST_PAGE_ITEMS_ONLY_MAX = 25;
                    // Fixed grid: lines 1–25 page 1, lines 26–47 page 2 — never spill rows to page 3.
                    var FIXED_TWO_PAGE_MAX_LINES = 47;
                    var showLayoutDebug = /(?:\?|&)layout_debug=1(?:&|$)/.test(window.location.search);
                    var isFirstPage = true;
                    var activePage = null;

                    function pageExceedsBudget(page) {
                        if (!page) {
                            return false;
                        }
                        var contentHeight = Math.max(page.offsetHeight, page.scrollHeight);
                        return contentHeight > (usableHeight + tolerance);
                    }

                    function pageFits(page, extraReserve) {
                        return pageContentHeight(page) <= (usableHeight - tolerance - (extraReserve || 0));
                    }

                    function clearPrintLayoutOverrides(pages) {
                        pages.forEach(function (page) {
                            page.style.height = '';
                            page.style.maxHeight = '';
                            page.style.minHeight = '';
                            page.classList.remove('print-page--fit');
                        });
                    }

                    function compressPageIfNeeded(page) {
                        if (!page) {
                            return false;
                        }
                        if (!page.classList.contains('print-page--fit')) {
                            page.classList.add('print-page--fit');
                        }
                        return pageExceedsBudget(page);
                    }

                    function enforceFirstPageRowBudget() {
                        var pages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                        var first = pages[0];
                        if (!first || singlePageDoc) {
                            return;
                        }
                        var tbody = first.querySelector('tbody');
                        if (!tbody) {
                            return;
                        }
                        compressPageIfNeeded(first);
                        while (pageExceedsBudget(first) && tbody.children.length > 1) {
                            var rowCount = tbody.children.length;
                            if (rowCount <= FIRST_PAGE_ITEMS_ONLY_MAX) {
                                break;
                            }
                            var row = tbody.lastElementChild;
                            tbody.removeChild(row);
                            activePage = null;
                            ensurePage();
                            activePage.tbody.insertBefore(row, activePage.tbody.firstChild);
                        }
                    }

                    function syncActivePage(pageEl) {
                        activePage = {
                            page: pageEl,
                            body: pageEl.querySelector('.print-page-body'),
                            table: pageEl.querySelector('table.items-table'),
                            tbody: pageEl.querySelector('tbody')
                        };
                    }

                    function ensureFooterPage() {
                        var pages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                        if (pages.length < 2) {
                            activePage = null;
                            ensurePage();
                            pages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                        }
                        syncActivePage(pages[pages.length - 1]);
                    }

                    pagesContainer.innerHTML = '';
                    pagesContainer.style.display = 'flex';
                    pagesContainer.setAttribute('data-measuring', 'true');
                    pagesContainer.style.visibility = 'hidden';
                    pagesContainer.style.position = 'absolute';
                    pagesContainer.style.left = '-9999px';
                    pagesContainer.style.right = 'auto';
                    pagesContainer.style.top = '0';
                    pagesContainer.style.width = '1000px';

                    function ensurePage() {
                        if (!activePage) {
                            activePage = createPage(pagesContainer, headerTemplate, theadTemplate, signatureTemplate, isFirstPage);
                            isFirstPage = false;
                        }
                    }

                    var singlePageDoc = rows.length <= MAX_ROWS_WITH_FOOTER;

                    if (singlePageDoc) {
                        // ≤22 lines: items + TOTAL + SIGNATURE all on page 1.
                        ensurePage();
                        rows.forEach(function (row) {
                            activePage.tbody.appendChild(row.cloneNode(true));
                        });
                    } else {
                        // >22 lines: page 1 = up to 25 items + SIGNATURE; last page = overflow + TOTAL + SIGNATURE.
                        rows.forEach(function (row) {
                            var clone = row.cloneNode(true);

                            ensurePage();

                            var pageEl = activePage.page;
                            var onFirstPage = pageEl.classList.contains('print-page--first');

                            if (onFirstPage && tbodyRowCount(pageEl) >= FIRST_PAGE_ITEMS_ONLY_MAX) {
                                activePage = null;
                                ensurePage();
                            }

                            activePage.tbody.appendChild(clone);
                        });
                    }

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
                        if (attr === 'data-page-remark') {
                            activePage.body.appendChild(clone);
                            return;
                        }
                        var bottom = ensurePrintPageBottom(activePage.page);
                        bottom.insertBefore(clone, bottom.querySelector('.print-page-footer'));
                    }

                    function fitLastPageWithFooter() {
                        for (var safety = 0; safety < rows.length + 5; safety++) {
                            var pages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                            var last = pages[pages.length - 1];
                            // Footer is already on the page — do not subtract trailingReserve again.
                            if (!last || pageFits(last, 0)) {
                                return;
                            }

                            var tbody = last.querySelector('tbody');
                            if (!tbody || !tbody.lastElementChild) {
                                return;
                            }

                            var row = tbody.lastElementChild;
                            tbody.removeChild(row);

                            activePage = null;
                            ensurePage();
                            var newPage = activePage.page;
                            activePage.tbody.insertBefore(row, activePage.tbody.firstChild);

                            var oldBottom = last.querySelector('.print-page-bottom');
                            if (oldBottom) {
                                var totalsBlock = oldBottom.querySelector('[data-page-totals-footer]');
                                if (totalsBlock) {
                                    var newBottom = ensurePrintPageBottom(newPage);
                                    var sigFooter = newBottom.querySelector('.print-page-footer');
                                    newBottom.insertBefore(totalsBlock, sigFooter);
                                }
                            }

                            var oldRemark = last.querySelector('[data-page-remark]');
                            if (oldRemark) {
                                var newBody = newPage.querySelector('.print-page-body');
                                if (newBody) {
                                    newBody.appendChild(oldRemark);
                                }
                            }

                            if (
                                tbody.children.length === 0
                                && !last.querySelector('.print-page-bottom')
                            ) {
                                last.parentNode.removeChild(last);
                            }
                        }
                    }

                    if (!singlePageDoc) {
                        ensureFooterPage();
                    } else if (activePage && activePage.page) {
                        syncActivePage(activePage.page);
                    }

                    appendBlock(remarkSource, 'data-page-remark');
                    appendBlock(totalsFooterSource, 'data-page-totals-footer');

                    var renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                    renderedPages.forEach(function (page) {
                        var tbody = page.querySelector('tbody');
                        var hasRows = tbody && tbody.children.length > 0;
                        var hasExtras = page.querySelector('[data-page-remark], [data-page-totals-footer], .print-page-bottom');
                        if (!hasRows && !hasExtras) {
                            page.parentNode.removeChild(page);
                        }
                    });

                    renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                    if (renderedPages.length > 0) {
                        applyPageNumberLabels(renderedPages);
                        if (isPrintLayout) {
                            enforceFirstPageRowBudget();
                            if (!singlePageDoc && rows.length > FIXED_TWO_PAGE_MAX_LINES) {
                                fitLastPageWithFooter();
                            }
                            renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                            applyPageNumberLabels(renderedPages);
                        } else {
                            clearPrintLayoutOverrides(renderedPages);
                        }
                    }

                    var headerHeight = headerTemplate ? measureNodeHeight(headerTemplate) : 0;
                    var theadHeight = theadTemplate ? measureNodeHeight(theadTemplate) : 0;
                    var rowHeight = 0;
                    if (rows[0]) {
                        var rowProbe = document.createElement('table');
                        rowProbe.className = 'items-table';
                        rowProbe.style.cssText = 'position:absolute;visibility:hidden;left:-9999px;width:1000px;';
                        var tbodyProbe = document.createElement('tbody');
                        tbodyProbe.appendChild(rows[0].cloneNode(true));
                        rowProbe.appendChild(tbodyProbe);
                        document.body.appendChild(rowProbe);
                        rowHeight = tbodyProbe.offsetHeight;
                        document.body.removeChild(rowProbe);
                    }
                    var contentBudget = usableHeight - tolerance - headerHeight - theadHeight - trailingReserve;
                    var estimatedRowsOnOnePage = rowHeight > 0 ? Math.max(0, Math.floor(contentBudget / rowHeight)) : 0;
                    logLayoutDiagnostics(renderedPages, {
                        pageHeight: pageHeight,
                        pagePaddingReserve: measurePrintPagePadding(),
                        usableHeight: usableHeight,
                        firstPageItemsOnlyMax: FIRST_PAGE_ITEMS_ONLY_MAX,
                        maxRowsWithFooter: MAX_ROWS_WITH_FOOTER,
                        headerHeight: headerHeight,
                        theadHeight: theadHeight,
                        rowHeight: rowHeight,
                        trailing: trailing,
                        estimatedRowsOnOnePage: estimatedRowsOnOnePage
                    });
                    if (showLayoutDebug) {
                        renderLayoutDiagnosticsPanel(window.__quotationLayoutDiagnostics.lines);
                    }

                    // Make pages-container visible and hide print-source
                    pagesContainer.style.visibility = '';
                    pagesContainer.style.position = '';
                    pagesContainer.style.left = '';
                    pagesContainer.style.right = '';
                    pagesContainer.style.top = '';
                    pagesContainer.style.width = '';
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
                    paginateQuotation(false, false);
                });
            }

            window.paginateQuotation = paginateQuotation;

            window.addEventListener('load', function () {
                paginateQuotation(true, false);
            });

            window.addEventListener('resize', schedulePaginate);

            window.addEventListener('afterprint', function () {
                pageHeightCache = null;
                setTimeout(function () {
                    paginateQuotation(true, false);
                }, 10);
            });
        })();
    </script>
</body>
</html>


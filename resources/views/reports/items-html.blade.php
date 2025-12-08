<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Listing</title>
    <style>
        @media print {
            @page {
                margin-top: 100px;  /* Reserve space for fixed header on EVERY page (85px header + 15px buffer) */
                margin-bottom: 15mm;
                margin-left: 15mm;
                margin-right: 15mm;
                size: A4;
            }
            
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
            
            html {
                margin: 0;
                padding: 0;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
                position: relative;
            }
            
            /* Fixed header - appears on every page when printing */
            /* The header sits in the @page margin-top area (top: 0) */
            .print-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                width: 100%;
                background: white;
                z-index: 1000;
                padding: 8px 15mm;
                border-bottom: 1px solid #000;
                box-sizing: border-box;
                height: 85px;
                max-height: 85px;
                min-height: 85px;
                overflow: hidden;
                margin: 0;
                page-break-inside: avoid;
                page-break-after: avoid;
                display: block !important; /* Ensure it's visible when printing */
            }
            
            /* Ensure body content starts below the margin area */
            body > .content-wrapper {
                margin-top: 0;
            }
            
            /* Hide screen header when printing */
            .screen-header {
                display: none !important;
            }
            
            /* Content wrapper - ensure proper spacing on all pages */
            .content-wrapper {
                margin: 0 !important;
                margin-top: 0 !important; /* @page margin-top will push this down */
                padding: 0 !important;
                position: relative;
                z-index: 1;
            }
            
            /* Spacer element - hidden, @page margin handles all spacing */
            .print-spacer {
                display: none !important;
            }
            
            /* Ensure table headers repeat on each page */
            thead {
                display: table-header-group;
            }
            
            /* Add spacing above repeating table headers on new pages */
            thead tr:first-child {
                margin-top: 0;
            }
            
            tbody {
                display: table-row-group;
            }
            
            /* Prevent breaking rows across pages */
            tr {
                page-break-inside: avoid;
            }
            
            .gh {
                page-break-inside: avoid;
            }
            
            .no-print {
                display: none !important;
            }
            
            /* Ensure table doesn't overlap header */
            .content-wrapper table {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
            
            /* Ensure table starts with proper spacing on every page */
            /* This helps when content flows to new pages */
            .content-wrapper > table {
                margin-top: 0;
            }
            
            /* Add spacing before table on page breaks if needed */
            .content-wrapper > table:first-child {
                margin-top: 0;
            }
            
            /* Ensure thead (table header) has proper spacing when it repeats on new pages */
            thead::before {
                content: '';
                display: block;
                height: 0;
                margin: 0;
                padding: 0;
            }
        }
        
        body { font-family: Arial; font-size: 11px; margin: 8px; }
        .header { margin-bottom: 6px; border-bottom: 1px solid #000; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 3px 4px; font-size: 10px; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .gh { background-color: #e0e0e0; font-weight: bold; }
        .n { text-align: right; }
        .q { text-align: center; }
        .no-print { display: none; }
        .print-header { display: none; } /* Hidden by default, shown only when printing */
        .print-spacer { display: none; } /* Hidden on screen */
        
        @media screen {
            .no-print { display: block; margin: 20px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc; }
            .print-header { display: none !important; }
            .screen-header { display: block; }
            .print-spacer { display: none !important; }
        }
    </style>
    <script>
        // Ensure print header is visible when printing
        window.addEventListener('beforeprint', function() {
            var printHeader = document.querySelector('.print-header');
            if (printHeader) {
                printHeader.style.display = 'block';
            }
        });
        
        window.addEventListener('afterprint', function() {
            var printHeader = document.querySelector('.print-header');
            if (printHeader && window.matchMedia('screen').matches) {
                printHeader.style.display = 'none';
            }
        });
    </script>
</head>
<body>
    <div class="no-print">
        <h2>Stock Listing Report</h2>
        <p>This is an HTML version of your report. You can:</p>
        <ul>
            <li>Print this page to PDF using your browser's print function (Ctrl+P or Cmd+P)</li>
            <li>Save this page as HTML for offline viewing</li>
        </ul>
        <p><strong>Note:</strong> For datasets with more than 3000 items, HTML format is used instead of PDF to ensure reliable generation.</p>
    </div>

    <!-- Print header (fixed on every page when printing) -->
    <div class="print-header">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                <td style="text-align: left; font-weight: bold; font-size: 13px; padding: 2px; border: none;">{{ $companyProfile->company_name ?? 'UNITED REFRIGERATION SYSTEM (M) SDN BHD' }}</td>
                <td style="text-align: right; font-size: 11px; padding: 2px; border: none;">DATE : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d/m/Y') }}<br>TIME : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('H:i:s') }}</td>
            </tr>
        </table>
        <div style="text-align: center; font-weight: bold; font-size: 16px; margin-top: 4px;">STOCK LISTING</div>
    </div>

    <!-- Screen header (visible on screen only) -->
    <div class="screen-header" style="margin-bottom: 6px; border-bottom: 1px solid #000; padding-bottom: 3px;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                <td style="text-align: left; font-weight: bold; font-size: 13px; padding: 2px; border: none;">{{ $companyProfile->company_name ?? 'UNITED REFRIGERATION SYSTEM (M) SDN BHD' }}</td>
                <td style="text-align: right; font-size: 11px; padding: 2px; border: none;">DATE : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d/m/Y') }}<br>TIME : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('H:i:s') }}</td>
            </tr>
        </table>
        <div style="text-align: center; font-weight: bold; font-size: 16px; margin-top: 4px;">STOCK LISTING</div>
    </div>

    <div class="content-wrapper">
    <!-- Spacer to push content below fixed header when printing -->
    <div class="print-spacer"></div>
    <table>
        <thead>
            <tr>
                <th>Stock Code</th>
                <th>Stock Description</th>
                @if(isset($columns['qty']))
                <th>Quantity</th>
                @endif
                @if(isset($columns['cost']))
                <th>Cost</th>
                @endif
                @if(isset($columns['cash_price']))
                <th>Cash</th>
                @endif
                @if(isset($columns['term_price']))
                <th>Term</th>
                @endif
                @if(isset($columns['cust_price']))
                <th>Customer</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if(isset($useGrouping) && $useGrouping)
                @php
                    $prevGroup = '';
                    $prevBrand = '';
                    $prevType = '';
                    $colCount = 2 + (isset($columns) ? count($columns) - 2 : 5);
                    $itemsArray = is_array($items) ? array_values($items) : $items->values()->all();
                @endphp
                @foreach($items as $index => $item)
                    @php
                        $groupName = trim($item->group_name ?? '');
                        $brandName = trim($item->family_name ?? '');
                        $typeName = trim($item->cat_name ?? '');
                        // Treat "UNDEFINED" as empty
                        if (strtoupper($typeName) === 'UNDEFINED') {
                            $typeName = '';
                        }
                        
                        $showGroup = $groupName !== $prevGroup;
                        $showBrand = $showGroup || ($brandName !== $prevBrand);
                        $showType = $showGroup || $showBrand || ($typeName !== $prevType);
                        
                        // Check if next item is different group (need blank line before next group)
                        $nextItem = $itemsArray[$index + 1] ?? null;
                        $needsBlankLine = false;
                        if ($nextItem) {
                            $nextGroup = trim($nextItem->group_name ?? '');
                            $nextBrand = trim($nextItem->family_name ?? '');
                            $nextType = trim($nextItem->cat_name ?? '');
                            // Treat "UNDEFINED" as empty for comparison
                            if (strtoupper($nextType) === 'UNDEFINED') {
                                $nextType = '';
                            }
                            $needsBlankLine = ($nextGroup !== $groupName) || ($nextBrand !== $brandName) || ($nextType !== $typeName);
                        }
                        
                        $prevGroup = $groupName;
                        $prevBrand = $brandName;
                        $prevType = $typeName;
                    @endphp
                    
                    @if($showGroup || $showBrand || $showType)
                    <tr>
                        <td class="gh" colspan="{{ $colCount }}" style="border: 1px solid #000; padding: 2px;">
                            <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
                                <tr style="border: none;">
                                    <td style="text-align: left; border: none; padding: 0; width: 33%;">GROUP: {{ $groupName }}</td>
                                    <td style="text-align: center; border: none; padding: 0; width: 34%;">BRAND: {{ $brandName }}</td>
                                    <td style="text-align: right; border: none; padding: 0; width: 33%;">TYPE: {{ $typeName }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    
                    <tr>
                        <td>{{ $item->item_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        @if(isset($columns['qty']))
                        <td class="q">{{ $item->qty !== null ? number_format($item->qty, 0) : '' }}</td>
                        @endif
                        @if(isset($columns['cost']))
                        <td class="n">{{ $item->cost ? number_format($item->cost, 2) : '' }}</td>
                        @endif
                        @if(isset($columns['cash_price']))
                        <td class="n">{{ $item->cash_price ? number_format($item->cash_price, 2) : '' }}</td>
                        @endif
                        @if(isset($columns['term_price']))
                        <td class="n">{{ $item->term_price ? number_format($item->term_price, 2) : '' }}</td>
                        @endif
                        @if(isset($columns['cust_price']))
                        <td class="n">{{ $item->cust_price ? number_format($item->cust_price, 2) : '' }}</td>
                        @endif
                    </tr>
                    
                    @if($needsBlankLine)
                    <tr>
                        <td colspan="{{ $colCount }}" style="height: 10px; border: none;"></td>
                    </tr>
                    @endif
                @endforeach
            @else
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->item_code }}</td>
                    <td>{{ $item->item_name }}</td>
                    @if(isset($columns['qty']))
                    <td class="q">{{ $item->qty !== null ? number_format($item->qty, 0) : '' }}</td>
                    @endif
                    @if(isset($columns['cost']))
                    <td class="n">{{ $item->cost ? number_format($item->cost, 2) : '' }}</td>
                    @endif
                    @if(isset($columns['cash_price']))
                    <td class="n">{{ $item->cash_price ? number_format($item->cash_price, 2) : '' }}</td>
                    @endif
                    @if(isset($columns['term_price']))
                    <td class="n">{{ $item->term_price ? number_format($item->term_price, 2) : '' }}</td>
                    @endif
                    @if(isset($columns['cust_price']))
                    <td class="n">{{ $item->cust_price ? number_format($item->cust_price, 2) : '' }}</td>
                    @endif
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    </div>
</body>
</html>





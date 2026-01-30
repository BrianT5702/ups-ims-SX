<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Listing</title>
    <style>
        @page {
            margin: 0.75cm;
            size: A4 portrait;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        
        .header {
            margin-bottom: 7px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }
        
        .company-name {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 4px;
            text-align: center;
        }
        
        .report-title {
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            margin: 4px 0;
        }
        
        .date-range {
            text-align: center;
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        table { 
            width: 100%; 
            max-width: 100%;
            border-collapse: collapse; 
            margin-top: 8px;
            margin-bottom: 0;
            font-size: 12px;
            page-break-inside: auto;
            table-layout: fixed;
        }
        
        thead {
            display: table-header-group;
        }
        
        tbody {
            display: table-row-group;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        tbody tr {
            height: auto;
            margin: 0;
            padding: 0;
            page-break-inside: avoid;
        }
        
        tbody tr:last-child {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        tbody tr:last-child td {
            border-bottom: 1px solid #000;
        }
        
        th, td { 
            border: 1px solid #000; 
            padding: 3px 4px; 
            text-align: left;
            word-wrap: break-word;
            overflow: hidden;
            max-width: 0;
        }
        
        td {
            font-size: 8px;
        }
        
        th { 
            background-color: #f0f0f0; 
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        
        .gh { 
            background-color: #e0e0e0; 
            font-weight: bold; 
        }
        
        .n { 
            text-align: right; 
        }
        
        .q { 
            text-align: center; 
        }
        
        /* Column width classes */
        .col-code {
            width: 20%;
        }
        
        .col-desc {
            width: 36%;
        }
        
        .col-qty {
            width: 4%;
        }
        
        .col-cost {
            width: 7.5%;
        }
        
        .col-cash {
            width: 7.5%;
        }
        
        .col-term {
            width: 7.5%;
        }
        
        .col-cust {
            width: 7.5%;
        }
        
        .col-amount {
            width: 10%;
        }
    </style>
</head>
<body>
    @php
        $itemsCollection = is_array($items) ? collect($items) : $items;
        
        // Calculate grand total if needed
        $grandTotal = 0;
        if (isset($showTotals) && $showTotals) {
            foreach ($itemsCollection as $item) {
                $qty = $item->qty ?? 0;
                $cost = $item->cost ?? 0;
                $grandTotal += ($qty * $cost);
            }
        }
    @endphp
    
    <div class="header">
        <div class="company-name">{{ $companyProfile->company_name ?? 'UNITED REFRIGERATION SYSTEM (M) SDN BHD' }}</div>
        <div class="report-title">STOCK LISTING</div>
        <div class="date-range">
            DATE : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d/m/Y') }} | TIME : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('H:i:s') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-code">Stock Code</th>
                <th class="col-desc">Stock Description</th>
                @if(isset($columns['qty']))
                <th class="col-qty">Qty</th>
                @endif
                @if(isset($columns['cost']))
                <th class="col-cost">Cost</th>
                @endif
                @if(isset($columns['cash_price']))
                <th class="col-cash">Cash</th>
                @endif
                @if(isset($columns['term_price']))
                <th class="col-term">Term</th>
                @endif
                @if(isset($columns['cust_price']))
                <th class="col-cust">Cust</th>
                @endif
                @if(isset($showTotals) && $showTotals)
                <th class="col-amount">Amount</th>
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
                    if (isset($showTotals) && $showTotals) {
                        $colCount++; // Add Amount column
                    }
                    $itemsArray = is_array($items) ? array_values($items) : $itemsCollection->values()->all();
                    $currentGroupKey = '';
                    $groupSubtotal = 0;
                @endphp
                @foreach($itemsCollection as $index => $item)
                    @php
                        $groupName = trim($item->group_name ?? '');
                        $brandName = trim($item->family_name ?? '');
                        $typeName = trim($item->cat_name ?? '');
                        // Treat "UNDEFINED" as empty
                        if (strtoupper($typeName) === 'UNDEFINED') {
                            $typeName = '';
                        }
                        
                        $currentKey = $groupName . '|' . $brandName . '|' . $typeName;
                        $showGroup = $groupName !== $prevGroup;
                        $showBrand = $showGroup || ($brandName !== $prevBrand);
                        $showType = $showGroup || $showBrand || ($typeName !== $prevType);
                        $isNewGroup = ($currentKey !== $currentGroupKey);
                        
                        // Check if next item is different group (need blank line before next group)
                        $nextItem = $itemsArray[$index + 1] ?? null;
                        $needsBlankLine = false;
                        $isLastInGroup = false;
                        if ($nextItem) {
                            $nextGroup = trim($nextItem->group_name ?? '');
                            $nextBrand = trim($nextItem->family_name ?? '');
                            $nextType = trim($nextItem->cat_name ?? '');
                            // Treat "UNDEFINED" as empty for comparison
                            if (strtoupper($nextType) === 'UNDEFINED') {
                                $nextType = '';
                            }
                            $nextKey = $nextGroup . '|' . $nextBrand . '|' . $nextType;
                            $needsBlankLine = ($nextKey !== $currentKey);
                            $isLastInGroup = ($nextKey !== $currentKey);
                        } else {
                            $isLastInGroup = true; // This is the last item overall
                        }
                        
                        // Update group tracking - reset subtotal when starting new group
                        if ($isNewGroup) {
                            // Reset subtotal for the new group (previous group's subtotal was already shown when it ended)
                            if ($currentGroupKey !== '') {
                                $groupSubtotal = 0; // Reset for new group
                            }
                            $currentGroupKey = $currentKey;
                        }
                        
                        $prevGroup = $groupName;
                        $prevBrand = $brandName;
                        $prevType = $typeName;
                    @endphp
                    
                    @if($showGroup || $showBrand || $showType)
                    <tr>
                        <td class="gh" colspan="{{ $colCount }}" style="border: 1px solid #000; padding: 4px; font-size: 9px;">
                            <div style="float: left; width: 33%;">GROUP: {{ $groupName }}</div>
                            <div style="float: left; width: 34%; text-align: center;">BRAND: {{ $brandName }}</div>
                            <div style="float: right; width: 33%; text-align: right;">TYPE: {{ $typeName }}</div>
                            <div style="clear: both;"></div>
                        </td>
                    </tr>
                    @endif
                    
                    @php
                        $amount = 0;
                        if (isset($showTotals) && $showTotals) {
                            $qty = $item->qty ?? 0;
                            $cost = $item->cost ?? 0;
                            $amount = $qty * $cost;
                            $groupSubtotal += $amount;
                        }
                    @endphp
                    
                    <tr>
                        <td class="col-code">{{ $item->item_code }}</td>
                        <td class="col-desc">{{ $item->item_name }}</td>
                        @if(isset($columns['qty']))
                        <td class="col-qty q">{{ $item->qty !== null ? number_format($item->qty, 0) : '' }}</td>
                        @endif
                        @if(isset($columns['cost']))
                        <td class="col-cost n">{{ $item->cost ? number_format($item->cost, 2) : '' }}</td>
                        @endif
                        @if(isset($columns['cash_price']))
                        <td class="col-cash n">{{ $item->cash_price ? number_format($item->cash_price, 2) : '' }}</td>
                        @endif
                        @if(isset($columns['term_price']))
                        <td class="col-term n">{{ $item->term_price ? number_format($item->term_price, 2) : '' }}</td>
                        @endif
                        @if(isset($columns['cust_price']))
                        <td class="col-cust n">{{ $item->cust_price ? number_format($item->cust_price, 2) : '' }}</td>
                        @endif
                        @if(isset($showTotals) && $showTotals)
                        <td class="col-amount n">{{ number_format($amount, 2) }}</td>
                        @endif
                    </tr>
                    
                    @if($isLastInGroup && isset($showTotals) && $showTotals)
                    @php
                        // Calculate final subtotal for this group before displaying
                        $finalGroupSubtotal = $groupSubtotal;
                        $groupSubtotal = 0; // Reset for next group
                    @endphp
                    <tr style="border-top: 1px solid #000;">
                        <td colspan="{{ $colCount - 1 }}" style="text-align: right; font-weight: bold; padding: 3px;">Sub Total:</td>
                        <td class="n" style="font-weight: bold; padding: 3px;">{{ number_format($finalGroupSubtotal, 2) }}</td>
                    </tr>
                    @endif
                    
                    @if($needsBlankLine)
                    <tr>
                        <td colspan="{{ $colCount }}" style="height: 10px; border: none;"></td>
                    </tr>
                    @endif
                @endforeach
            @else
                @foreach($itemsCollection as $item)
                @php
                    $amount = 0;
                    if (isset($showTotals) && $showTotals) {
                        $qty = $item->qty ?? 0;
                        $cost = $item->cost ?? 0;
                        $amount = $qty * $cost;
                    }
                @endphp
                <tr>
                    <td class="col-code">{{ $item->item_code }}</td>
                    <td class="col-desc">{{ $item->item_name }}</td>
                    @if(isset($columns['qty']))
                    <td class="col-qty q">{{ $item->qty !== null ? number_format($item->qty, 0) : '' }}</td>
                    @endif
                    @if(isset($columns['cost']))
                    <td class="col-cost n">{{ $item->cost ? number_format($item->cost, 2) : '' }}</td>
                    @endif
                    @if(isset($columns['cash_price']))
                    <td class="col-cash n">{{ $item->cash_price ? number_format($item->cash_price, 2) : '' }}</td>
                    @endif
                    @if(isset($columns['term_price']))
                    <td class="col-term n">{{ $item->term_price ? number_format($item->term_price, 2) : '' }}</td>
                    @endif
                    @if(isset($columns['cust_price']))
                    <td class="col-cust n">{{ $item->cust_price ? number_format($item->cust_price, 2) : '' }}</td>
                    @endif
                    @if(isset($showTotals) && $showTotals)
                    <td class="col-amount n">{{ number_format($amount, 2) }}</td>
                    @endif
                </tr>
                @endforeach
            @endif
            @if(isset($showTotals) && $showTotals)
            @php
                $colCountTotal = 2;
                if (isset($columns['qty'])) $colCountTotal++;
                if (isset($columns['cost'])) $colCountTotal++;
                if (isset($columns['cash_price'])) $colCountTotal++;
                if (isset($columns['term_price'])) $colCountTotal++;
                if (isset($columns['cust_price'])) $colCountTotal++;
                $colCountTotal++; // For Amount column
            @endphp
            <tr style="border-top: 2px solid #000;">
                <td colspan="{{ $colCountTotal - 1 }}" style="text-align: right; font-weight: bold; padding: 5px;">Grand Total:</td>
                <td class="n" style="font-weight: bold; padding: 5px;">{{ number_format($grandTotal ?? 0, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>
    
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Arial");
            $size = 8;
            $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $y = $pdf->get_height() - 20;
            $x = $pdf->get_width() - 80;
            $pdf->page_text($x, $y, $pageText, $font, $size);
        }
    </script>
</body>
</html>

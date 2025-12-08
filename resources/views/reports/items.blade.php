<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Listing</title>
    <style>
        body { font-family: Arial; font-size: 11px; margin: 8px; }
        .header { margin-bottom: 6px; border-bottom: 1px solid #000; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 3px 4px; font-size: 10px; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .gh { background-color: #e0e0e0; font-weight: bold; }
        .n { text-align: right; }
        .q { text-align: center; }
    </style>
</head>
<body>
    <div style="margin-bottom: 6px; border-bottom: 1px solid #000; padding-bottom: 3px;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                <td style="text-align: left; font-weight: bold; font-size: 13px; padding: 2px; border: none;">{{ $companyProfile->company_name ?? 'UNITED REFRIGERATION SYSTEM (M) SDN BHD' }}</td>
                <td style="text-align: right; font-size: 11px; padding: 2px; border: none;">DATE : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d/m/Y') }}<br>TIME : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('H:i:s') }}</td>
            </tr>
        </table>
        <div style="text-align: center; font-weight: bold; font-size: 16px; margin-top: 4px;">STOCK LISTING</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Stock Code</th>
                <th>Stock Description</th>
                @if(isset($columns['qty']))
                <th>Quantity</th>
                @endif
                @if(isset($columns['cost']))
                <th>Cost Price</th>
                @endif
                @if(isset($columns['cash_price']))
                <th>Cash Price</th>
                @endif
                @if(isset($columns['term_price']))
                <th>Term Price</th>
                @endif
                @if(isset($columns['cust_price']))
                <th>Customer</th>
                @endif
                @if(isset($showTotals) && $showTotals)
                <th>Amount</th>
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
                    $itemsArray = is_array($items) ? array_values($items) : $items->values()->all();
                    $currentGroupKey = '';
                    $groupSubtotal = 0;
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
                        @if(isset($showTotals) && $showTotals)
                        <td class="n">{{ number_format($amount, 2) }}</td>
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
                @foreach($items as $item)
                @php
                    $subtotal = 0;
                    if (isset($showTotals) && $showTotals) {
                        $qty = $item->qty ?? 0;
                        $cost = $item->cost ?? 0;
                        $subtotal = $qty * $cost;
                    }
                @endphp
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
                    @if(isset($showTotals) && $showTotals)
                    <td class="n">{{ number_format($amount, 2) }}</td>
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
</body>
</html>

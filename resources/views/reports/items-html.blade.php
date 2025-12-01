<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Listing</title>
    <style>
        @media print {
            @page { margin: 15mm; }
        }
        body { font-family: Arial; font-size: 8px; margin: 8px; }
        .header { margin-bottom: 6px; border-bottom: 1px solid #000; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 1px 2px; font-size: 6px; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .gh { background-color: #e0e0e0; font-weight: bold; }
        .n { text-align: right; }
        .q { text-align: center; }
        .no-print { display: none; }
        @media screen {
            .no-print { display: block; margin: 20px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc; }
        }
    </style>
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

    <div style="margin-bottom: 6px; border-bottom: 1px solid #000; padding-bottom: 3px;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                <td style="text-align: left; font-weight: bold; font-size: 10px; padding: 2px; border: none;">{{ $companyProfile->company_name ?? 'UNITED REFRIGERATION SYSTEM (M) SDN BHD' }}</td>
                <td style="text-align: right; font-size: 8px; padding: 2px; border: none;">DATE : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d/m/Y') }}<br>TIME : {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('H:i:s') }}</td>
            </tr>
        </table>
        <div style="text-align: center; font-weight: bold; font-size: 12px; margin-top: 4px;">STOCK LISTING</div>
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
                        <td class="q">{{ $item->qty === 0 ? '0' : ($item->qty ?: '') }}</td>
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
                    <td class="q">{{ $item->qty === 0 ? '0' : ($item->qty ?: '') }}</td>
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
</body>
</html>





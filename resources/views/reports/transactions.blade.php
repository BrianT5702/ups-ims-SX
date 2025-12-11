<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Balance Report</title>
    <style>
        @page {
            margin: 0.75cm;
            size: A4 portrait;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        
        .print-page {
            position: relative;
            min-height: calc(29.7cm - 1.5cm);
            page-break-after: always;
        }
        
        .print-page--last {
            page-break-after: auto;
        }
        
        .print-page::after {
            content: 'Page ' attr(data-page-number) ' of ' attr(data-total-pages);
            position: absolute;
            bottom: 0.5cm;
            right: 0.75cm;
            font-size: 10px;
            font-family: Arial, sans-serif;
            color: #000;
        }
        
        .header {
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }
        
        .company-name {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 4px;
            text-align: center;
        }
        
        .report-title {
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            margin: 4px 0;
        }
        
        .date-range {
            text-align: center;
            font-size: 11px;
            margin-bottom: 12px;
        }
        
        .report-params {
            display: table;
            width: 100%;
            margin-top: 12px;
            font-size: 10px;
        }
        
        .report-params-row {
            display: table-row;
        }
        
        .report-params-cell {
            display: table-cell;
            padding: 1px 4px;
        }
        
        .report-params-cell.label {
            font-weight: bold;
            width: 20%;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 8px;
            font-size: 10px;
        }
        
        th, td { 
            border: 1px solid #000; 
            padding: 3px 4px; 
            text-align: left;
        }
        
        th { 
            background-color: #f0f0f0; 
            font-weight: bold;
            text-align: center;
        }
        
        .col-no {
            width: 4%;
            text-align: center;
        }
        
        .col-code {
            width: 12%;
        }
        
        .col-desc {
            width: 35%;
        }
        
        .col-uom {
            width: 6%;
            text-align: center;
        }
        
        .col-qty {
            width: 8%;
            text-align: right;
        }
        
        .footer {
            margin-top: 8px;
            border-top: 1px solid #000;
            padding-top: 4px;
            font-size: 10px;
        }
        
        .footer-row {
            margin: 2px 0;
        }
        
        .total-row {
            font-weight: bold;
            border-top: 1px solid #000;
        }
        
        .total-row td {
            padding: 4px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        @media print {
            .print-page::after {
                display: block;
            }
        }
    </style>
</head>
<body>
    @php
        $itemsPerPage = 35; // Approximate items per page
        $totalItems = $stockBalances->count();
        $totalPages = ceil($totalItems / $itemsPerPage);
        $currentPage = 1;
        $itemNumber = 1;
        
        // Calculate totals
        $totalBF = $stockBalances->sum('bf');
        $totalIN = $stockBalances->sum('in');
        $totalOUT = $stockBalances->sum('out');
        $totalBALANCE = $stockBalances->sum('balance');
    @endphp
    
    @foreach($stockBalances->chunk($itemsPerPage) as $pageItems)
        <div class="print-page @if($currentPage == $totalPages) print-page--last @endif" data-page-number="{{ $currentPage }}" data-total-pages="{{ $totalPages }}">
            <div class="header">
                <div class="company-name">{{ $companyProfile->company_name ?? 'UNITED REFRIGERATION SYSTEM (M) SDN BHD' }}</div>
                <div class="report-title">STOCK BALANCE REPORT</div>
                <div class="date-range">({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} To {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})</div>
                
                <div class="report-params">
                    <div class="report-params-row">
                        <div class="report-params-cell label">STOCK:</div>
                        <div class="report-params-cell">{{ $stockFilter }}</div>
                        <div class="report-params-cell label">CATEGORY:</div>
                        <div class="report-params-cell">{{ $categoryName }}</div>
                    </div>
                    <div class="report-params-row">
                        <div class="report-params-cell label">FAMILY:</div>
                        <div class="report-params-cell">{{ $familyName }}</div>
                        <div class="report-params-cell label">GROUP:</div>
                        <div class="report-params-cell">{{ $groupName }}</div>
                        <div class="report-params-cell label">Date:</div>
                        <div class="report-params-cell">{{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th class="col-no">NO</th>
                        <th class="col-code">STOCK CODE</th>
                        <th class="col-desc">DESCRIPTION</th>
                        <th class="col-uom">UOM</th>
                        <th class="col-qty">B/F</th>
                        <th class="col-qty">IN</th>
                        <th class="col-qty">OUT</th>
                        <th class="col-qty">BALANCE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pageItems as $item)
                        <tr>
                            <td class="text-center">{{ $itemNumber }}</td>
                            <td>{{ $item['item_code'] }}</td>
                            <td>{{ $item['item_name'] }}</td>
                            <td class="text-center">{{ $item['um'] ?? 'PCS' }}</td>
                            <td class="text-right">{{ number_format($item['bf'], 0) }}</td>
                            <td class="text-right">{{ number_format($item['in'], 0) }}</td>
                            <td class="text-right">{{ number_format($item['out'], 0) }}</td>
                            <td class="text-right">{{ number_format($item['balance'], 0) }}</td>
                        </tr>
                        @php $itemNumber++; @endphp
                    @endforeach
                    
                    @if($currentPage == $totalPages)
                        <tr>
                            <td colspan="3" style="font-weight: bold; padding: 4px;">Total Stock Record Listed: {{ $totalItems }}</td>
                            <td class="text-right" style="font-weight: bold;">TOTAL:</td>
                            <td class="text-right" style="font-weight: bold;">{{ number_format($totalBF, 0) }}</td>
                            <td class="text-right" style="font-weight: bold;">{{ number_format($totalIN, 0) }}</td>
                            <td class="text-right" style="font-weight: bold;">{{ number_format($totalOUT, 0) }}</td>
                            <td class="text-right" style="font-weight: bold;">{{ number_format($totalBALANCE, 0) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            
            @if($currentPage == $totalPages)
                <div class="footer">
                    <div class="footer-row">Printed on: {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d/m/Y, H:i:s') }}</div>
                </div>
            @endif
        </div>
        @php $currentPage++; @endphp
    @endforeach
</body>
</html>

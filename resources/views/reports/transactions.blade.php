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
            page-break-after: always;
            min-height: 0;
            height: auto;
            margin: 0;
            padding: 0;
            display: block;
        }
        
        .print-page:not(:first-child) {
            page-break-before: always;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        .print-page--last {
            page-break-after: auto !important;
        }
        
        .page-number {
            position: absolute;
            bottom: 0.1cm;
            right: 0.75cm;
            text-align: right;
            font-size: 12px;
            font-family: Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1;
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
            margin-bottom: 0;
            font-size: 12px;
            page-break-inside: auto;
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
        
        /* Prevent table from breaking too early */
        tbody {
            page-break-inside: avoid;
        }
        
        /* Ensure proper spacing - prevent excessive blank space */
        .print-page {
            overflow: visible;
        }
        
        th, td { 
            border: 1px solid #000; 
            padding: 3px 4px; 
            text-align: left;
        }
        
        td {
            font-size: 10.5px;
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
            width: 18%;
        }
        
        .col-desc {
            width: 42%;
        }
        
        .col-uom {
            width: 6%;
            text-align: center;
        }
        
        .col-qty {
            width: 5%;
            text-align: right;
        }
        
        .col-balance {
            width: 5%;
            text-align: right;
        }
        
        .footer {
            margin-top: 4px;
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
        // Calculate items per page - use conservative number to avoid blank pages
        // A4 portrait: ~27cm height
        // Header (company name, title, date, params): ~4.5cm
        // Footer (page number, printed date): ~1.5cm
        // Margins: 0.75cm top + 0.75cm bottom = 1.5cm
        // Available for table: 27 - 4.5 - 1.5 - 1.5 = 19.5cm
        // Each row is approximately 0.45-0.5cm
        // Safe calculation: 19.5 / 0.5 = 39 rows max
        // Use 35-37 items per page to ensure no blank pages and proper spacing
        $itemsPerPage = 36; // Conservative number to prevent blank pages
        
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
    
    @php
        $chunks = $stockBalances->chunk($itemsPerPage)->filter(function($chunk) {
            return !$chunk->isEmpty();
        });
        $actualTotalPages = $chunks->count();
    @endphp
    
    @foreach($chunks as $chunkIndex => $pageItems)
        @php
            $isLastPage = ($chunkIndex + 1) == $actualTotalPages;
        @endphp
        <div class="print-page @if($isLastPage) print-page--last @endif" data-page-number="{{ $currentPage }}" data-total-pages="{{ $actualTotalPages }}">
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
                        <th class="col-balance">BALANCE</th>
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
                    
                    @if($isLastPage)
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
            
            <div class="page-number">Page {{ $currentPage }} of {{ $actualTotalPages }}</div>
            
            @if($isLastPage)
                <div class="footer" style="margin-top: 4px;">
                    <div class="footer-row">Printed on: {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d/m/Y, H:i:s') }}</div>
                </div>
            @endif
        </div>
        @php $currentPage++; @endphp
    @endforeach
</body>
</html>

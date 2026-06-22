<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Order List</title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: Arial, sans-serif;
            color: #212529;
            margin: 0;
            padding: 16px;
            font-size: 12px;
            line-height: 1.3;
        }

        .print-header {
            margin-bottom: 12px;
        }

        .print-header h1 {
            font-size: 18px;
            margin: 0 0 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .print-header .meta {
            font-size: 11px;
            color: #6c757d;
            margin: 0;
        }

        .print-header .meta + .meta {
            margin-top: 2px;
        }

        .table-wrap {
            overflow-x: auto;
            max-width: 100%;
        }

        table {
            width: max-content;
            min-width: 100%;
            border-collapse: collapse;
            border: 1px solid #212529;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 4px 6px;
            vertical-align: middle;
            text-align: left;
            white-space: nowrap;
        }

        thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 11px;
            border-bottom: 2px solid #212529;
        }

        thead th:first-child,
        tbody td:first-child {
            border-left: 1px solid #212529;
        }

        thead th:last-child,
        tbody td:last-child {
            border-right: 1px solid #212529;
        }

        tbody tr:last-child td {
            border-bottom: 1px solid #212529;
        }

        tbody td {
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .do-status {
            font-weight: 600;
        }

        .do-status.posted {
            color: #198754;
        }

        .do-status.unposted {
            color: #dc3545;
        }

        .do-print-flag {
            font-weight: 500;
        }

        .do-row-cancelled,
        .do-row-cancelled .do-print-flag,
        .do-row-cancelled .do-status {
            color: #b02a37 !important;
        }

        .col-user,
        .col-datetime {
            font-size: 9px;
        }

        .empty-row td {
            text-align: center;
            padding: 16px;
        }

        .screen-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .screen-actions button,
        .screen-actions a {
            padding: 6px 14px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            color: #212529;
            background: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 4px;
            line-height: 1.4;
        }

        .screen-actions button:hover,
        .screen-actions a:hover {
            background: #e9ecef;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body {
                padding: 0;
            }

            .screen-actions {
                display: none !important;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="screen-actions">
        <a href="{{ route('delivery-orders') }}">Back</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <div class="print-header">
        <h1>
            @if($filteredCustomer)
                Delivery Orders — {{ $filteredCustomer->cust_name }}
            @else
                Delivery Order List
            @endif
        </h1>
        @if($periodLabel)
            <p class="meta">Period: {{ $periodLabel }}</p>
        @endif
        @if($searchTerm)
            <p class="meta">Search: {{ $searchTerm }}</p>
        @endif
        <p class="meta">Total: {{ $deliveryOrders->count() }} delivery order(s) · Printed {{ $printedAt }}</p>
    </div>

    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>DO Number</th>
                <th>Date</th>
                <th>Customer Name</th>
                <th>Amount</th>
                @if($showInvoiceNoColumn)
                    <th>Invoice No</th>
                @endif
                <th>Salesman</th>
                <th>Status</th>
                <th class="text-center">Print</th>
                <th class="col-user">Created by</th>
                <th class="col-user">Last edited by</th>
                <th class="col-datetime">Last edited at</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliveryOrders as $deliveryOrder)
                @php
                    $isCancelledStyle = ((int) ($deliveryOrder->items_count ?? 0) === 0);
                @endphp
                <tr class="{{ $isCancelledStyle ? 'do-row-cancelled' : '' }}">
                    <td>{{ $deliveryOrder->do_num }}</td>
                    <td>{{ $deliveryOrder->date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $deliveryOrder->customerSnapshot->cust_name ?? $deliveryOrder->customer->cust_name }}</td>
                    <td>{{ $deliveryOrder->customerSnapshot->currency ?? $deliveryOrder->customer->currency ?? 'RM' }} {{ number_format($deliveryOrder->total_amount ?? 0, 2) }}</td>
                    @if($showInvoiceNoColumn)
                        <td>{{ $deliveryOrder->invoice_no ?? '' }}</td>
                    @endif
                    <td>{{ $deliveryOrder->salesman ? strtoupper($deliveryOrder->salesman->username) : '-' }}</td>
                    <td>
                        @php
                            $isPosted = ($deliveryOrder->status ?? 'Completed') === 'Completed';
                        @endphp
                        <span class="do-status {{ $isPosted ? 'posted' : 'unposted' }}">
                            {{ $isPosted ? 'Post' : 'Unpost' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="do-print-flag">
                            {{ $deliveryOrder->printed === 'Y' ? 'Y' : 'N' }}
                        </span>
                    </td>
                    <td class="col-user">{{ $deliveryOrder->user->name ?? '-' }}</td>
                    <td class="col-user">{{ $deliveryOrder->updatedBy->name ?? ($deliveryOrder->user->name ?? '-') }}</td>
                    <td class="col-datetime">
                        {{ $deliveryOrder->updated_at
                            ? \Carbon\Carbon::parse($deliveryOrder->updated_at)->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i')
                            : '—' }}
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="{{ $showInvoiceNoColumn ? 11 : 10 }}">No delivery orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</body>
</html>

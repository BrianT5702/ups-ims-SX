<div class="list-page-unified-density">
    <div class="container my-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h5 class="fw-bold mb-1 list-page-unified-title">Consumption Dashboard</h5>
                        <p class="text-muted small mb-0 chemical-dash-subtitle">
                            Comparing {{ $currentMonthName }} with {{ $comparisonMonthName }}
                        </p>
                    </div>
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                        <label for="comparison-month" class="form-label mb-0">Compare with</label>
                        <select wire:model.live="selectedComparisonMonth" id="comparison-month" class="form-select form-select-sm rounded" style="min-width: 11rem;">
                            @foreach($availableMonths as $month)
                                <option value="{{ $month['value'] }}" {{ $month['value'] === $currentMonth->format('Y-m') ? 'disabled' : '' }}>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- IBC Chemical Section -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="chemical-section-heading mb-0">IBC chemical consumption</h6>
                        <span class="small text-muted">Total records: {{ count($ibcData) }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered chemical-dashboard-table">
                            <thead>
                                <tr>
                                    <th>Chemical Code</th>
                                    <th class="text-end">{{ $currentMonthName }} (IBCT)</th>
                                    <th class="text-end">{{ $comparisonMonthName }} (IBCT)</th>
                                    <th class="text-end">Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ibcData as $data)
                                    <tr>
                                        <td class="fw-semibold">{{ $data->che_code }}</td>
                                        <td class="text-end">{{ number_format($data->current_month_qty, 2) }}</td>
                                        <td class="text-end">{{ number_format($data->comparison_month_qty, 2) }}</td>
                                        <td class="text-end {{ $data->difference >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($data->difference, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Loading/Unloading Section -->
                <div class="mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="chemical-section-heading mb-0">Loading / unloading chemical percentage</h6>
                        <span class="small text-muted">Total records: {{ count($loadingUnloadingData) }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered chemical-dashboard-table">
                            <thead>
                                <tr>
                                    <th>Chemical Code</th>
                                    <th class="text-end">{{ $currentMonthName }} (%)</th>
                                    <th class="text-end">{{ $comparisonMonthName }} (%)</th>
                                    <th class="text-end">Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loadingUnloadingData as $data)
                                    <tr>
                                        <td class="fw-semibold">{{ $data->che_code }}</td>
                                        <td class="text-end">{{ number_format($data->current_month_percentage, 2) }}%</td>
                                        <td class="text-end">{{ number_format($data->comparison_month_percentage, 2) }}%</td>
                                        <td class="text-end {{ $data->difference >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($data->difference, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Incoming QC Section -->
                <div class="mb-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="chemical-section-heading mb-0">Incoming quality control</h6>
                        <span class="small text-muted">Total records: {{ count($incomingQCData) }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered chemical-dashboard-table">
                            <thead>
                                <tr>
                                    <th>Chemical Code</th>
                                    <th class="text-end">{{ $currentMonthName }} quantity</th>
                                    <th class="text-end">{{ $comparisonMonthName }} quantity</th>
                                    <th class="text-end">Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomingQCData as $data)
                                    <tr>
                                        <td class="fw-semibold">{{ $data->che_code }}</td>
                                        <td class="text-end">{{ number_format($data->current_month_qty, 2) }}</td>
                                        <td class="text-end">{{ number_format($data->comparison_month_qty, 2) }}</td>
                                        <td class="text-end {{ $data->difference >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($data->difference, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .list-page-unified-density .list-page-unified-title {
            font-size: 1.25rem;
        }
        .list-page-unified-density .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #2f3b4b;
        }
        .list-page-unified-density .form-select-sm {
            font-size: 0.8rem;
            min-height: calc(1.35em + 0.35rem + 2px);
            padding-top: 0.18rem;
            padding-bottom: 0.18rem;
        }
        .chemical-dash-subtitle {
            font-size: 0.8rem;
            line-height: 1.25;
        }
        .chemical-section-heading {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #5f6f86;
        }
        .table.chemical-dashboard-table {
            --bs-table-border-color: #d0d7e2;
            table-layout: fixed;
            width: 100%;
        }
        .table.chemical-dashboard-table > :not(caption) > * > * {
            border-color: var(--bs-table-border-color);
        }
        .table.chemical-dashboard-table thead th {
            background-color: #f4f6fa;
            font-size: 0.78rem;
            line-height: 1.4;
            vertical-align: middle;
            padding: 0.5rem;
        }
        .table.chemical-dashboard-table tbody td {
            font-size: 0.8rem;
            line-height: 1.25;
            padding: 0.5rem;
            vertical-align: middle;
            word-wrap: break-word;
        }
    </style>
</div>

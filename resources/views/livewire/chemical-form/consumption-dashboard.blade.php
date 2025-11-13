<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Consumption Dashboard</h2>
                    <div class="text-sm text-gray-600 mt-1">
                        Comparing {{ $currentMonthName }} with {{ $comparisonMonthName }}
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <label for="comparison-month" class="text-sm font-medium text-gray-700">Compare With:</label>
                        <select wire:model.live="selectedComparisonMonth" id="comparison-month" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($availableMonths as $month)
                                <option value="{{ $month['value'] }}" {{ $month['value'] === $currentMonth->format('Y-m') ? 'disabled' : '' }}>
                                    {{ $month['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- IBC Chemical Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">IBC Chemical Consumption</h3>
                    <div class="text-sm text-gray-600">Total Records: {{ count($ibcData) }}</div>
                </div>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full table-fixed">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="w-1/4 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chemical Code</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $currentMonthName }} (IBCT)</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $comparisonMonthName }} (IBCT)</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($ibcData as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $data->che_code }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($data->current_month_qty, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($data->comparison_month_qty, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right {{ $data->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($data->difference, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-sm text-gray-500">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Loading/Unloading Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Loading/Unloading Chemical Percentage</h3>
                    <div class="text-sm text-gray-600">Total Records: {{ count($loadingUnloadingData) }}</div>
                </div>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full table-fixed">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="w-1/4 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chemical Code</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $currentMonthName }} (%)</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $comparisonMonthName }} (%)</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($loadingUnloadingData as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $data->che_code }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($data->current_month_percentage, 2) }}%</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($data->comparison_month_percentage, 2) }}%</td>
                                    <td class="px-4 py-3 text-sm text-right {{ $data->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($data->difference, 2) }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-sm text-gray-500">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Incoming QC Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Incoming Quality Control</h3>
                    <div class="text-sm text-gray-600">Total Records: {{ count($incomingQCData) }}</div>
                </div>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full table-fixed">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="w-1/4 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chemical Code</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $currentMonthName }} Quantity</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $comparisonMonthName }} Quantity</th>
                                <th class="w-1/4 px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($incomingQCData as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $data->che_code }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($data->current_month_qty, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($data->comparison_month_qty, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right {{ $data->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($data->difference, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-center text-sm text-gray-500">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-4">Generate Inventory Report</h2>
        
        @if($errorMessage)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <strong>Error:</strong> {{ $errorMessage }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Report Format</label>
            <select wire:model="fileType" class="rounded-md shadow-sm border-gray-300 w-full">
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
            </select>
        </div>

        <div class="mb-4 grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Filter by Group</label>
                <select wire:model="selectedGroupId" class="rounded-md shadow-sm border-gray-300 w-full">
                    <option value="">All Groups</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Filter by Brand</label>
                <select wire:model="selectedFamilyId" class="rounded-md shadow-sm border-gray-300 w-full">
                    <option value="">All Brands</option>
                    @foreach($families as $family)
                        <option value="{{ $family->id }}">{{ $family->family_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Filter by Type</label>
                <select wire:model="selectedCategoryId" class="rounded-md shadow-sm border-gray-300 w-full">
                    <option value="">All Types</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->cat_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Stock Quantity Filter</label>
            <div class="grid grid-cols-3 gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" wire:model="stockFilter" value="all" class="rounded border-gray-300">
                    <span class="ml-2">All</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" wire:model="stockFilter" value="gt0" class="rounded border-gray-300">
                    <span class="ml-2">Print Non-Zero Quantity Only</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" wire:model="stockFilter" value="eq0" class="rounded border-gray-300">
                    <span class="ml-2">Print Zero Quantity Only</span>
                </label>
            </div>
        </div>

        <!--         <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Show Grouping Headers (GROUP/BRAND/TYPE)</label>
            <label class="inline-flex items-center">
                <input type="checkbox" 
                    wire:model="showGrouping" 
                    class="rounded border-gray-300">
                <span class="ml-2">Show GROUP/BRAND/TYPE headers in report</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">Note: Grouping is automatically disabled for datasets with more than 3000 items to improve performance</p>
        </div> -->

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Select Columns</label>
            <div class="grid grid-cols-3 gap-4">
                @foreach($availableColumns as $value => $label)
                    <label class="inline-flex items-center">
                        <input type="checkbox" 
                            wire:model="selectedColumns" 
                            value="{{ $value }}"
                            @if(in_array($value, ['item_code', 'item_name'])) checked disabled @endif
                            class="rounded border-gray-300">
                        <span class="ml-2">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <button 
            wire:click="generateReport" 
            wire:loading.attr="disabled"
            wire:target="generateReport"
            type="button"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 relative disabled:opacity-50 disabled:cursor-not-allowed w-48 flex items-center justify-center"
            @if($isGenerating) disabled @endif
        >
            <div wire:loading.remove wire:target="generateReport">
                Generate Report
            </div>
            <div wire:loading wire:target="generateReport" class="flex items-center justify-center space-x-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Generating...</span>
            </div>
        </button>

        @if($reportJobToken && !$reportDownloadUrl)
            <div wire:poll.2s="checkReportStatus" class="mt-4">
                <div class="flex items-center justify-between text-sm text-blue-700 mb-1">
                    <span>{{ $reportStatusMessage ?: 'Generating PDF in background...' }}</span>
                    <span>{{ (int) $reportProgress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                        class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                        style="width: {{ max(0, min(100, (int) $reportProgress)) }}%;"
                    ></div>
                </div>
            </div>
        @endif

        @if(!empty($reportHistory))
            <div class="mt-6" x-data="{ openHistory: false }">
                <h3 class="text-lg font-semibold mb-3">Recent Report Timeline</h3>
                @php
                    $latestHistory = $reportHistory[0] ?? null;
                    $olderHistory = array_slice($reportHistory, 1);
                @endphp

                <div class="space-y-3">
                    @if($latestHistory)
                        @php
                            $status = $latestHistory['status'] ?? 'queued';
                            $statusColor = match($status) {
                                'ready' => 'bg-green-100 text-green-700 border-green-200',
                                'failed' => 'bg-red-100 text-red-700 border-red-200',
                                'processing' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                default => 'bg-blue-100 text-blue-700 border-blue-200',
                            };
                            $dotColor = match($status) {
                                'ready' => 'bg-green-500',
                                'failed' => 'bg-red-500',
                                'processing' => 'bg-yellow-500',
                                default => 'bg-blue-500',
                            };
                            $filters = $latestHistory['filters'] ?? [];
                        @endphp
                        <div class="relative border rounded-lg p-4 bg-white shadow-sm">
                            <div class="absolute left-0 top-0 h-full w-1 rounded-l-lg {{ $dotColor }}"></div>
                            <div class="pl-2">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-2">
                                    <div class="text-sm font-semibold text-gray-800">
                                        {{ strtoupper($latestHistory['file_type'] ?? 'pdf') }} report (Latest)
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColor }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>

                                <div class="text-sm text-gray-700 mb-2">
                                    {{ $latestHistory['message'] ?? 'No status message' }}
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-600">
                                    <div><span class="font-medium text-gray-700">Progress:</span> {{ (int) ($latestHistory['progress'] ?? 0) }}%</div>
                                    <div><span class="font-medium text-gray-700">Queued:</span> {{ $latestHistory['queued_at'] ?? '-' }}</div>
                                    <div><span class="font-medium text-gray-700">Updated:</span> {{ $latestHistory['updated_at'] ?? '-' }}</div>
                                </div>

                                <div class="mt-2 text-xs text-gray-600">
                                    <span class="font-medium text-gray-700">Filters:</span>
                                    Group={{ $filters['group'] ?? 'All Groups' }},
                                    Brand={{ $filters['brand'] ?? 'All Brands' }},
                                    Type={{ $filters['type'] ?? 'All Types' }},
                                    Stock={{ $filters['stock_filter'] ?? 'All quantities' }}
                                </div>

                                @if(!empty($latestHistory['download_url']))
                                    <div class="mt-3">
                                        <a href="{{ $latestHistory['download_url'] }}" class="inline-flex items-center text-sm text-green-700 hover:text-green-800 font-medium">
                                            Download this report
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                @if(count($olderHistory) > 0)
                    <button
                        type="button"
                        @click="openHistory = !openHistory"
                        class="mt-3 inline-flex items-center text-sm text-blue-700 hover:text-blue-800 font-medium"
                    >
                        <span x-show="!openHistory">Show older reports ({{ count($olderHistory) }})</span>
                        <span x-show="openHistory">Hide older reports</span>
                    </button>

                    <div x-show="openHistory" x-transition class="mt-3 space-y-3">
                        @foreach($olderHistory as $history)
                            @php
                                $status = $history['status'] ?? 'queued';
                                $statusColor = match($status) {
                                    'ready' => 'bg-green-100 text-green-700 border-green-200',
                                    'failed' => 'bg-red-100 text-red-700 border-red-200',
                                    'processing' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    default => 'bg-blue-100 text-blue-700 border-blue-200',
                                };
                                $dotColor = match($status) {
                                    'ready' => 'bg-green-500',
                                    'failed' => 'bg-red-500',
                                    'processing' => 'bg-yellow-500',
                                    default => 'bg-blue-500',
                                };
                                $filters = $history['filters'] ?? [];
                            @endphp
                            <div class="relative border rounded-lg p-4 bg-white shadow-sm">
                                <div class="absolute left-0 top-0 h-full w-1 rounded-l-lg {{ $dotColor }}"></div>
                                <div class="pl-2">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-2">
                                        <div class="text-sm font-semibold text-gray-800">
                                            {{ strtoupper($history['file_type'] ?? 'pdf') }} report
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColor }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </div>

                                    <div class="text-sm text-gray-700 mb-2">
                                        {{ $history['message'] ?? 'No status message' }}
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-600">
                                        <div><span class="font-medium text-gray-700">Progress:</span> {{ (int) ($history['progress'] ?? 0) }}%</div>
                                        <div><span class="font-medium text-gray-700">Queued:</span> {{ $history['queued_at'] ?? '-' }}</div>
                                        <div><span class="font-medium text-gray-700">Updated:</span> {{ $history['updated_at'] ?? '-' }}</div>
                                    </div>

                                    <div class="mt-2 text-xs text-gray-600">
                                        <span class="font-medium text-gray-700">Filters:</span>
                                        Group={{ $filters['group'] ?? 'All Groups' }},
                                        Brand={{ $filters['brand'] ?? 'All Brands' }},
                                        Type={{ $filters['type'] ?? 'All Types' }},
                                        Stock={{ $filters['stock_filter'] ?? 'All quantities' }}
                                    </div>

                                    @if(!empty($history['download_url']))
                                        <div class="mt-3">
                                            <a href="{{ $history['download_url'] }}" class="inline-flex items-center text-sm text-green-700 hover:text-green-800 font-medium">
                                                Download this report
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div class="mt-8" x-data="{ openReports: false }">
            <button
                type="button"
                @click="openReports = !openReports"
                class="w-full border rounded-lg px-4 py-3 bg-white hover:bg-gray-50 transition flex items-center justify-between"
            >
                <div class="text-left">
                    <h3 class="text-lg font-semibold">Available Reports (Last 7 Days)</h3>
                    <p class="text-xs text-gray-500">Older files are auto-removed after 7 days</p>
                </div>
                <svg x-show="!openReports" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg x-show="openReports" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>

            <div x-show="openReports" x-transition class="mt-3">
                @if(!empty($availableReports))
                    <div class="border rounded-lg divide-y bg-white">
                        @foreach($availableReports as $reportFile)
                            <div class="p-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                <div>
                                    <div class="text-sm font-medium text-gray-800">{{ $reportFile['filename'] }}</div>
                                    <div class="text-xs text-gray-600">
                                        Generated: {{ $reportFile['generated_at'] }} | Size: {{ $reportFile['size_kb'] }} KB
                                    </div>
                                </div>
                                <a href="{{ $reportFile['download_url'] }}" class="inline-flex items-center text-sm text-blue-700 hover:text-blue-800 font-medium">
                                    Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="border rounded-lg p-4 text-sm text-gray-600 bg-gray-50">
                        No generated reports available in the last 7 days.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
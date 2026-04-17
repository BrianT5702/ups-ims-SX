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

        @if($reportDownloadUrl)
            <div class="mt-4">
                <a href="{{ $reportDownloadUrl }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    Download Ready PDF
                </a>
            </div>
        @endif
    </div>
</div>
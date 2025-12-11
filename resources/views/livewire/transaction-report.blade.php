<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-4">Generate Stock Balance Report</h2>
        
        @if($errorMessage)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ $errorMessage }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-2">Start Date</label>
                <input 
                    type="date" 
                    wire:model="startDate"
                    class="rounded-md shadow-sm border-gray-300 w-full"
                    max="{{ $endDate }}"
                >
                @error('startDate') 
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">End Date</label>
                <input 
                    type="date" 
                    wire:model="endDate"
                    class="rounded-md shadow-sm border-gray-300 w-full"
                    min="{{ $startDate }}"
                >
                @error('endDate') 
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Transaction Type</label>
                <select 
                    wire:model="selectedTransactionType" 
                    class="rounded-md shadow-sm border-gray-300 w-full"
                >
                    @foreach($transactionTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('selectedTransactionType') 
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium mb-2">Stock Filter</label>
                <select wire:model="stockFilter" class="rounded-md shadow-sm border-gray-300 w-full">
                    <option value="all">All Stock</option>
                    <option value="gt0">Stock > 0</option>
                    <option value="eq0">Stock = 0</option>
                </select>
                @error('stockFilter') 
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Group</label>
                <select wire:model="selectedGroupId" class="rounded-md shadow-sm border-gray-300 w-full">
                    <option value="">All Groups</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Family</label>
                <select wire:model="selectedFamilyId" class="rounded-md shadow-sm border-gray-300 w-full">
                    <option value="">All Families</option>
                    @foreach($families as $family)
                        <option value="{{ $family->id }}">{{ $family->family_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select wire:model="selectedCategoryId" class="rounded-md shadow-sm border-gray-300 w-full">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->cat_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Report Format</label>
            <select wire:model="fileType" class="rounded-md shadow-sm border-gray-300 w-full">
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
            </select>
        </div>

        <button 
            wire:click="generateReport" 
            wire:loading.attr="disabled"
            type="button"
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 relative disabled:opacity-50 disabled:cursor-not-allowed w-48 flex items-center justify-center"
            @if($isGenerating) disabled @endif
        >
            <div wire:loading.remove>
                Generate Report
            </div>
            <div wire:loading class="flex items-center justify-center space-x-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Generating...</span>
            </div>
        </button>
    </div>
</div>
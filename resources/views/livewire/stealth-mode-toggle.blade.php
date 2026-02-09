<div class="p-6">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Transaction Stealth Mode</h1>

        @if (session('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Current Status</h2>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg mb-4">
                    <div>
                        <p class="text-lg font-medium text-gray-800">
                            Stealth Mode: 
                            <span class="{{ $isActive ? 'text-red-600' : 'text-green-600' }} font-bold">
                                {{ $isActive ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </p>
                        @if ($lastChanged)
                            <p class="text-sm text-gray-600 mt-2">
                                Last changed: {{ \Carbon\Carbon::parse($lastChanged)->format('d/m/Y H:i:s') }}
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center">
                        <div class="relative inline-block w-16 h-8 rounded-full cursor-pointer transition-colors duration-200 ease-in-out {{ $isActive ? 'bg-red-500' : 'bg-gray-300' }}" 
                             wire:click="toggle">
                            <div class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-md transform transition-transform duration-200 ease-in-out {{ $isActive ? 'translate-x-8' : 'translate-x-0' }}"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold mb-3 text-gray-700">What is Stealth Mode?</h3>
                <div class="space-y-3 text-gray-600">
                    <p>
                        <strong class="text-gray-800">When ACTIVE:</strong>
                        All transaction data is completely hidden from non-super-admin users. They will see empty transaction logs, reports, and history.
                    </p>
                    <p>
                        <strong class="text-gray-800">When INACTIVE:</strong>
                        Transaction data is visible to all authorized users according to their permissions.
                    </p>
                    <p class="text-sm text-gray-500 mt-4">
                        <strong>Note:</strong> Only Super Admin can toggle this feature. Transaction creation will continue to work for system operations, but viewing/querying transactions will be restricted.
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t">
                <button 
                    wire:click="toggle"
                    class="w-full px-6 py-3 rounded-lg font-semibold text-white transition-colors duration-200 {{ $isActive ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}"
                >
                    {{ $isActive ? 'Deactivate Stealth Mode' : 'Activate Stealth Mode' }}
                </button>
            </div>
        </div>
    </div>
</div>


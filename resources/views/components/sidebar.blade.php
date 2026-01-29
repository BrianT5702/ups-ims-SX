
<div x-data="{
        isSidebarOpen: JSON.parse(localStorage.getItem('isSidebarOpen')) ?? true,
        infoSectionOpen: JSON.parse(localStorage.getItem('infoSectionOpen')) ?? false,
        inventorySectionOpen: JSON.parse(localStorage.getItem('inventorySectionOpen')) ?? false,
        chemicalSectionOpen: JSON.parse(localStorage.getItem('chemicalSectionOpen')) ?? false,
        reportSectionOpen: JSON.parse(localStorage.getItem('reportSectionOpen')) ?? false,
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
            localStorage.setItem('isSidebarOpen', JSON.stringify(this.isSidebarOpen));
        },
        toggleInfoSection() {
            this.infoSectionOpen = !this.infoSectionOpen;
            localStorage.setItem('infoSectionOpen', JSON.stringify(this.infoSectionOpen));
        },
        toggleInventorySection() {
            this.inventorySectionOpen = !this.inventorySectionOpen;
            localStorage.setItem('inventorySectionOpen', JSON.stringify(this.inventorySectionOpen));
        },
        toggleChemicalSection() {
            this.chemicalSectionOpen = !this.chemicalSectionOpen;
            localStorage.setItem('chemicalSectionOpen', JSON.stringify(this.chemicalSectionOpen));
        },
        toggleReportSection() {
            this.reportSectionOpen = !this.reportSectionOpen;
            localStorage.setItem('reportSectionOpen', JSON.stringify(this.reportSectionOpen));
        }
    }" class="flex h-screen bg-gray-200 text-black sticky top-0" >

    <!-- Sidebar -->
    <div :class="isSidebarOpen ? 'w-60' : 'w-5'" class="bg-gray-200 text-black h-screen transition-all duration-300 ease-in-out sticky top-0">

        <!-- Sidebar Header (Hidden when collapsed) -->
        <div x-show="isSidebarOpen" class="block hover:bg-gray-400 px-4 py-2 bg-gray-300 truncate">
        <a href="{{route('dashboard')}}"> Dashboard </a>
        </div>

        <!-- Collapsible Section: Information (Hidden when collapsed) -->
        <div x-show="isSidebarOpen" class="my-2">
            <button @click="toggleInfoSection" class="flex justify-between w-full px-4 py-2 text-left bg-gray-300 hover:bg-gray-400 focus:outline-none">
                <span x-show="isSidebarOpen" class="truncate">Setup</span>
                <svg x-show="!infoSectionOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg x-show="infoSectionOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
            <div x-show="infoSectionOpen" class="px-4 py-2 bg-gray-100">
                @can('Edit Company Profile')
                    <a href="{{route('profiles')}}" class="block px-2 py-1 hover:bg-gray-300">Company Profile</a>
                @endcan
                @can('Manage User')
                    <a href="{{route('users')}}" class="block px-2 py-1 hover:bg-gray-300">User</a>    
                @endcan    
                @can('Manage Category')
                    <a href="{{route('categories')}}" class="block px-2 py-1 hover:bg-gray-300">Category</a>
                @endcan
                @can('Manage Family')
                    <a href="{{route('families')}}" class="block px-2 py-1 hover:bg-gray-300">Family</a>
                @endcan
                @can('Manage Group')
                    <a href="{{route('groups')}}" class="block px-2 py-1 hover:bg-gray-300">Group</a>
                @endcan
                @can('Manage Customer')
                    <a href="{{route('customers')}}" class="block px-2 py-1 hover:bg-gray-300">Customer</a>
                @endcan
                @can('Manage Supplier')
                    <a href="{{route('suppliers')}}" class="block px-2 py-1 hover:bg-gray-300">Supplier</a> 
                @endcan
                @can('Manage Inventory')
                    <a href="{{route('show-import-form')}}" class="block px-2 py-1 hover:bg-gray-300">Import Excel</a>
                @endcan
                @can('Manage Inventory')
                    <a href="{{route('show-delete-form')}}" class="block px-2 py-1 hover:bg-gray-300 text-red-600">Delete Records</a>
                @endcan
                     
            </div>
        </div>

        <!-- Collapsible Section: Inventory (Hidden when collapsed) -->
        <div x-show="isSidebarOpen" class="my-2">
            <button @click="toggleInventorySection" class="flex justify-between w-full px-4 py-2 text-left bg-gray-300 hover:bg-gray-400 focus:outline-none">
                <span x-show="isSidebarOpen" class="truncate">Inventory</span>
                <svg x-show="!inventorySectionOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg x-show="inventorySectionOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
            <div x-show="inventorySectionOpen" class="px-4 py-2 bg-gray-100">
                @can('Manage Inventory')
                    <a href="{{route('items')}}" class="block px-2 py-1 hover:bg-gray-300">Manage Inventory</a>
                @endcan
                @can('Manage DO')
                    <a href="{{route('delivery-orders')}}" class="block px-2 py-1 hover:bg-gray-300">Delivery Order</a>
                @endcan
                @can('Manage DO')
                    <a href="{{route('quotations')}}" class="block px-2 py-1 hover:bg-gray-300">Quotation</a>
                @endcan
                @can('Manage PO')
                    <a href="{{route('purchase-orders')}}" class="block px-2 py-1 hover:bg-gray-300">Purchase Order</a>
                @endcan
                @can('Manage Restock List')
                    <a href="{{route('restock-list')}}" class="block px-2 py-1 hover:bg-gray-300 relative">Restock List</a>
                @endcan
                @can('View Transaction Log')
                    <a href="{{route('transaction-log.')}}" class="block px-2 py-1 hover:bg-gray-300">Transaction Log</a>
                @endcan
                @can('View Batch List')
                    <a href="{{route('batch-list')}}" class="block px-2 py-1 hover:bg-gray-300">Batch List</a>
                @endcan
                @can('Manage Warehouse')
                    <a href="{{route('warehouses')}}" class="block px-2 py-1 hover:bg-gray-300">Warehouse</a>
                @endcan
                @can('Manage Location')
                    <a href="{{route('locations')}}" class="block px-2 py-1 hover:bg-gray-300">Location</a>
                @endcan
            </div>
        </div>

                <!-- Collapsible Section: Chemical Consumption (Hidden when collapsed) -->
        <div x-show="isSidebarOpen" class="my-2">
            <button @click="toggleChemicalSection" class="flex justify-between w-full px-4 py-2 text-left bg-gray-300 hover:bg-gray-400 focus:outline-none">
                <span x-show="isSidebarOpen" class="truncate">Chemical Consumption</span>
                <svg x-show="!chemicalSectionOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg x-show="chemicalSectionOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
            
            <div x-show="chemicalSectionOpen" class="px-4 py-2 bg-gray-100">
            @can('View Consumption Form')
                    <a href="{{route('chemical.ibc')}}" class="block px-2 py-1 hover:bg-gray-300">IBC Chemical Stock</a>

                    <a href="{{route('chemical.loading-unloading')}}" class="block px-2 py-1 hover:bg-gray-300">Loading and Unloading</a>

                    <a href="{{route('chemical.iqc')}}" class="block px-2 py-1 hover:bg-gray-300">Incoming Quality Control</a>

                    <a href="{{route('chemical.consumption-dashboard')}}" class="block px-2 py-1 hover:bg-gray-300 relative">Overview</a>
                @endcan
            </div>
        </div>

        <!-- @can('View Report')
        <a href="{{route('report')}}" class="">
            <div x-show="isSidebarOpen" class="block hover:bg-gray-400 px-4 py-2 bg-gray-300 truncate">
                Report
            </div>
        </a>
        @endcan -->

        @can('View Report')
        <div x-show="isSidebarOpen" class="my-2">
            <button @click="toggleReportSection" class="flex justify-between w-full px-4 py-2 text-left bg-gray-300 hover:bg-gray-400 focus:outline-none">
                <span x-show="isSidebarOpen" class="truncate">Report</span>
                <svg x-show="!reportSectionOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg x-show="reportSectionOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
            <div x-show="reportSectionOpen" class="px-4 py-2 bg-gray-100">
                    <a href="{{route('report')}}" class="block px-2 py-1 hover:bg-gray-300">Inventory Report</a>
                    <a href="{{route('transaction-report')}}" class="block px-2 py-1 hover:bg-gray-300">Transaction Report</a>
            </div>
        </div>
        @endcan

        <div class="absolute top-1/2 -right-12 transform -translate-y-1/2">
            <button @click="toggleSidebar" class="p-2 bg-gray-300 hover:bg-gray-400 rounded-full focus:outline-none">
                <!-- Icon for collapsing/expanding -->
                <svg x-show="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5" />
                </svg>
                <svg x-show="!isSidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l-7-7 7-7m0 14l7-7-7-7" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-grow bg-gray-100 dark:bg-gray-900 p-6">
        <!-- Page content goes here -->
        {{ $slot }}
    </div>
</div>



@if (session()->has('stock-alert'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
        class="fixed bottom-4 right-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded shadow-md z-50"
        role="alert"
    >
        <p class="font-bold">Stock Alert</p>
        <p>Item '{{ session('stock-alert')['item_code'] }} : '{{ session('stock-alert')['item_name'] }}' has reached the stock alert level.</p>
        <p>Current stock: {{ session('stock-alert')['current_stock'] }}, Alert level: {{ session('stock-alert')['alert_level'] }}</p>
    </div>
@endif
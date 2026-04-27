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
        class="fixed bottom-3 right-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-2 rounded shadow-md z-50"
        style="max-width: 420px; font-size: 0.9rem; line-height: 1.3;"
        role="alert"
    >
        <p class="font-bold mb-1">Stock Alert</p>
        <p class="mb-1">Item '{{ session('stock-alert')['item_code'] }} : '{{ session('stock-alert')['item_name'] }}' has reached the stock alert level.</p>
        <p class="mb-0">Current stock: {{ session('stock-alert')['current_stock'] }}, Alert level: {{ session('stock-alert')['alert_level'] }}</p>
    </div>
@endif
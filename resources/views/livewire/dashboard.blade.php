<div>
    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            
            <!-- Timeframe Filter -->
            <div class="flex gap-4">
                <button 
                    wire:click="updateTimeframe('today')"
                    class="hover:underline px-4 py-2 rounded-lg {{ $timeframe === 'today' ? 'bg-blue-600 text-white' : 'bg-blue-200' }}"
                >
                    Today
                </button>
                <button 
                    wire:click="updateTimeframe('month')"
                    class="hover:underline px-4 py-2 rounded-lg {{ $timeframe === 'month' ? 'bg-blue-600 text-white' : 'bg-blue-200' }}"
                >
                    This Month
                </button>
                <button 
                    wire:click="updateTimeframe('year')"
                    class="hover:underline px-4 py-2 rounded-lg {{ $timeframe === 'year' ? 'bg-blue-600 text-white' : 'bg-blue-200' }}"
                >
                    This Year
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-3 gap-6 mb-6">
            <div class="p-4 bg-red-50 rounded-lg">
                <h3 class="text-lg font-medium text-red-800">Out of Stock Items</h3>
                <p class="text-3xl font-bold text-red-600">{{ $inventory['out_of_stock'] }}</p>
                <p class="text-sm text-red-700">Items with zero stock</p>
            </div>
            <div class="p-4 bg-yellow-50 rounded-lg">
                <h3 class="text-lg font-medium text-yellow-800">Low Stock Alert</h3>
                <p class="text-3xl font-bold text-yellow-600">{{ $inventory['below_alert_level'] }}</p>
                <p class="text-sm text-yellow-700">Items at or below alert level</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-800">Dead Stock</h3>
                <p class="text-3xl font-bold text-gray-600">{{ $inventory['dead_stock'] }}</p>
                <p class="text-sm text-gray-700">No movement in over a year</p>
            </div>
        </div>
        <!-- Orders Summary -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="p-4 bg-green-50 rounded-lg">
                <h3 class="text-lg font-medium text-green-800">Purchase Orders</h3>
                <p class="text-3xl font-bold text-green-600">{{ $totals['purchase_orders'] }}</p>
                <p class="text-sm text-green-700">Total for selected period</p>
            </div>
            <div class="p-4 bg-purple-50 rounded-lg">
                <h3 class="text-lg font-medium text-purple-800">Delivery Orders</h3>
                <p class="text-3xl font-bold text-purple-600">{{ $totals['delivery_orders'] }}</p>
                <p class="text-sm text-purple-700">Total for selected period</p>
            </div>
        </div>

        <!-- Total Revenue Card -->
        <div class="grid grid-cols-1 gap-6 mb-6">
            <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                <h3 class="text-lg font-medium text-green-800 mb-2">Total Revenue</h3>
                <p class="text-4xl font-bold text-green-600">RM {{ number_format($salesSummary['total_revenue'], 2) }}</p>
                <p class="text-sm text-green-700 mt-1">From completed delivery orders</p>
            </div>
        </div>
        
        <!-- Sales Analytics Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Trend Chart -->
            <div class="bg-white p-4 rounded-lg shadow-md" wire:key="revenue-trend-{{ $timeframe }}">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Revenue Trend</h3>
                <div class="w-full h-[300px]" x-data="{
                    chart: null,
                    chartData: @entangle('revenueTrendChartData'),
                    init() {
                        this.$watch('chartData', (newData) => {
                            if (this.chart) {
                                this.chart.destroy();
                            }
                            if (newData && newData.labels && newData.labels.length > 0) {
                                this.$nextTick(() => {
                                    this.initChart();
                                });
                            }
                        });
                        
                        if (this.chartData && this.chartData.labels && this.chartData.labels.length > 0) {
                            this.$nextTick(() => {
                                this.initChart();
                            });
                        }
                    },
                    initChart() {
                        if (!this.chartData || !this.$refs.canvas) return;
                        const ctx = this.$refs.canvas.getContext('2d');
                        this.chart = new Chart(ctx, {
                            type: 'line',
                            data: this.chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'RM ' + value.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: { display: true, position: 'top' }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas"></canvas>
                    <div x-show="!chartData || !chartData.labels || chartData.labels.length === 0" class="text-center text-gray-500 py-20">
                        No revenue data available for the selected period
                    </div>
                </div>
            </div>

            <!-- Top Products Chart -->
            <div class="bg-white p-4 rounded-lg shadow-md" wire:key="top-products-{{ $timeframe }}">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Top Products</h3>
                <div class="w-full h-[300px]" x-data="{
                    chart: null,
                    chartData: @entangle('topProductsChartData'),
                    init() {
                        this.$watch('chartData', (newData) => {
                            if (this.chart) {
                                this.chart.destroy();
                            }
                            if (newData && newData.labels && newData.labels.length > 0) {
                                this.$nextTick(() => {
                                    this.initChart();
                                });
                            }
                        });
                        
                        if (this.chartData && this.chartData.labels && this.chartData.labels.length > 0) {
                            this.$nextTick(() => {
                                this.initChart();
                            });
                        }
                    },
                    initChart() {
                        if (!this.chartData || !this.$refs.canvas) return;
                        const ctx = this.$refs.canvas.getContext('2d');
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: this.chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'RM ' + value.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: { display: true, position: 'top' }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas"></canvas>
                    <div x-show="!chartData || !chartData.labels || chartData.labels.length === 0" class="text-center text-gray-500 py-20">
                        No product data available for the selected period
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Orders Chart -->
        <div class="w-full h-[400px]" x-data="{
            chart: null,
            chartData: @entangle('chartData'),
            
            init() {
                this.$watch('chartData', (newData) => {
                    if (this.chart) {
                        this.chart.destroy();
                    }
                    if (newData) {
                        this.$nextTick(() => {
                            this.initChart();
                        });
                    }
                });
                
                if (this.chartData) {
                    this.$nextTick(() => {
                        this.initChart();
                    });
                }
            },
            
            initChart() {
                if (!this.chartData) return;
                
                const ctx = document.getElementById('ordersChart').getContext('2d');
                
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: this.chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 0
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                display: true,
                                grid: {
                                    display: true,
                                    drawBorder: true
                                },
                                ticks: {
                                    stepSize: 1,
                                    display: true
                                },
                                min: 0,
                                suggestedMax: 5
                            },
                            x: {
                                display: true,
                                grid: {
                                    display: true,
                                    drawBorder: true
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45,
                                    display: true
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Orders Distribution',
                                font: {
                                    size: 16
                                }
                            },
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15
                                }
                            }
                        },
                        layout: {
                            padding: {
                                top: 20,
                                right: 20,
                                bottom: 20,
                                left: 20
                            }
                        }
                    }
                });
            }
        }">
            <canvas id="ordersChart"></canvas>
        </div>
    </div>
</div>
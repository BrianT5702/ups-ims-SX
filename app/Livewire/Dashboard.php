<?php

namespace App\Livewire;

use App\Models\PurchaseOrder;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Item;
use App\Models\IBCChemical;
use App\Models\IncomingQualityControl;
use App\Models\Customer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $timeframe = 'today';
    public $chartData;
    public $revenueTrendChartData;
    public $topProductsChartData;
    public $startDate;
    public $endDate;
    public $interval;
    public $format;

    public function mount()
    {
        $this->updateTimeframe('today');
        $this->loadSalesCharts();
    }

    public function updateTimeframe($timeframe)
    {
        $this->timeframe = $timeframe;
        
        // Set date range and interval based on timeframe
        switch ($timeframe) {
            case 'today':
                $this->startDate = Carbon::today();
                $this->endDate = Carbon::now();
                $this->interval = '1 hour';
                $this->format = 'H:i';
                break;
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth();
                $this->endDate = Carbon::now();
                $this->interval = '1 day';
                $this->format = 'M d';
                break;
            case 'year':
                $this->startDate = Carbon::now()->startOfYear();
                $this->endDate = Carbon::now();
                $this->interval = '1 month';
                $this->format = 'M Y';
                break;
        }

        $this->loadChartData();
        $this->loadSalesCharts();
    }

    public function loadSalesCharts()
    {
        $this->revenueTrendChartData = $this->getRevenueTrendChart();
        $this->topProductsChartData = $this->getTopProductsChart();
    }

    public function loadChartData()
    {
        $period = CarbonPeriod::create($this->startDate, $this->interval, $this->endDate);
        
        $data = collect($period)->map(function ($date) {
            $endDate = $this->getEndDate($date);
            $startDate = $this->getStartDate($date);

            return [
                'time_label' => $date->format($this->format),
                'purchase_orders' => PurchaseOrder::whereBetween('created_at', [$startDate, $endDate])
                    ->where('po_num', '!=', 'PO0000000000')
                    ->count(),
                'delivery_orders' => DeliveryOrder::whereBetween('created_at', [$startDate, $endDate])->count(),
            ];
        });

        $this->chartData = [
            'labels' => $data->pluck('time_label')->toArray(),
            'datasets' => [
                [
                    'label' => 'Purchase Orders',
                    'backgroundColor' => 'rgba(38, 185, 154, 0.31)',
                    'borderColor' => 'rgba(38, 185, 154, 0.7)',
                    'data' => $data->pluck('purchase_orders')->toArray(),
                ],
                [
                    'label' => 'Delivery Orders',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.31)',
                    'borderColor' => 'rgba(153, 102, 255, 0.7)',
                    'data' => $data->pluck('delivery_orders')->toArray(),
                ]
            ]
        ];
    }

    private function getEndDate($date)
    {
        switch ($this->timeframe) {
            case 'today':
                return $date->copy()->addHour()->subSecond();
            case 'month':
                return $date->copy()->endOfDay();
            case 'year':
                return $date->copy()->endOfMonth();
        }
    }

    private function getStartDate($date)
    {
        switch ($this->timeframe) {
            case 'today':
                return $date->copy()->startOfHour();
            case 'month':
                return $date->copy()->startOfDay();
            case 'year':
                return $date->copy()->startOfMonth();
        }
    }

    public function getInventoryStats()
    {
        return [
            'out_of_stock' => Item::where('qty', 0)->count(),
            'below_alert_level' => Item::whereColumn('qty', '<=', 'stock_alert_level')
                ->where('qty', '>=', 0)
                ->count(),
            'dead_stock' => Item::where('updated_at', '<', Carbon::now()->subYear())->count(),
        ];
    }

    public function getTotalStats()
    {
        return [
            'purchase_orders' => PurchaseOrder::whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('po_num', '!=', 'PO0000000000')
                ->count(),
            'delivery_orders' => DeliveryOrder::whereBetween('created_at', [$this->startDate, $this->endDate])->count(),
        ];
    }

    public function getSalesSummary()
    {
        // Calculate total revenue from delivery orders in the selected period
        $totalRevenue = DeliveryOrder::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'Completed')
            ->whereNotNull('total_amount')
            ->sum('total_amount') ?? 0;

        return [
            'total_revenue' => $totalRevenue,
        ];
    }

    public function getRevenueTrendChart()
    {
        $period = CarbonPeriod::create($this->startDate, $this->interval, $this->endDate);
        
        $data = collect($period)->map(function ($date) {
            $endDate = $this->getEndDate($date);
            $startDate = $this->getStartDate($date);

            $revenue = DeliveryOrder::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'Completed')
                ->whereNotNull('total_amount')
                ->sum('total_amount') ?? 0;

            return [
                'time_label' => $date->format($this->format),
                'revenue' => $revenue,
            ];
        });

        return [
            'labels' => $data->pluck('time_label')->toArray(),
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.31)',
                    'borderColor' => 'rgba(34, 197, 94, 0.7)',
                    'data' => $data->pluck('revenue')->toArray(),
                ]
            ]
        ];
    }

    public function getTopCustomersChart()
    {
        $topCustomers = DeliveryOrder::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'Completed')
            ->whereNotNull('total_amount')
            ->where('total_amount', '>', 0)
            ->select('cust_id', DB::raw('SUM(total_amount) as total_revenue'))
            ->groupBy('cust_id')
            ->havingRaw('SUM(total_amount) > 0')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                // Try to get customer from snapshot first, then from customer table
                $customer = null;
                if ($order->customerSnapshot) {
                    $customer = (object)['cust_name' => $order->customerSnapshot->cust_name];
                } else {
                    $customer = Customer::find($order->cust_id);
                }
                return [
                    'name' => $customer ? $customer->cust_name : 'Unknown Customer',
                    'revenue' => $order->total_revenue ?? 0,
                ];
            })
            ->filter(function ($customer) {
                return $customer['revenue'] > 0; // Only include customers with revenue
            });

        return [
            'labels' => $topCustomers->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.31)',
                    'borderColor' => 'rgba(59, 130, 246, 0.7)',
                    'data' => $topCustomers->pluck('revenue')->toArray(),
                ]
            ]
        ];
    }

    public function getTopProductsChart()
    {
        $topProducts = DeliveryOrderItem::whereHas('deliveryOrder', function ($query) {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                      ->where('status', 'Completed');
            })
            ->select('item_id', DB::raw('SUM(COALESCE(amount, qty * unit_price, 0)) as total_revenue'))
            ->groupBy('item_id')
            ->havingRaw('SUM(COALESCE(amount, qty * unit_price, 0)) > 0')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $product = Item::find($item->item_id);
                // If item doesn't exist, try to get custom_item_name from delivery order items
                if (!$product) {
                    $doItem = DeliveryOrderItem::where('item_id', $item->item_id)
                        ->whereHas('deliveryOrder', function($q) {
                            $q->whereBetween('created_at', [$this->startDate, $this->endDate])
                              ->where('status', 'Completed');
                        })
                        ->whereNotNull('custom_item_name')
                        ->first();
                    // For deleted items, fall back to the custom item name (may be long but rare)
                    $name = $doItem ? $doItem->custom_item_name : null; // Skip if no custom name
                } else {
                    // Use item_code instead of item_name for the chart label; fallback to name if code missing
                    $name = $product->item_code ?: $product->item_name;
                }
                return [
                    'name' => $name,
                    'revenue' => $item->total_revenue ?? 0,
                ];
            })
            ->filter(function ($item) {
                // Only include items with revenue and valid name (exclude deleted items without custom name)
                return $item['revenue'] > 0 && !empty($item['name']);
            })
            ->values(); // Re-index array

        return [
            'labels' => $topProducts->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.31)',
                    'borderColor' => 'rgba(168, 85, 247, 0.7)',
                    'data' => $topProducts->pluck('revenue')->toArray(),
                ]
            ]
        ];
    }

    public function render()
    {
        // Load sales charts if not already loaded
        if (!isset($this->revenueTrendChartData)) {
            $this->loadSalesCharts();
        }
        
        $totals = $this->getTotalStats();
        $inventory = $this->getInventoryStats();
        $salesSummary = $this->getSalesSummary();
        return view('livewire.dashboard', compact('totals', 'inventory', 'salesSummary'))->layout('layouts.app');
    }
}
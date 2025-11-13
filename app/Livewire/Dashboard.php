<?php

namespace App\Livewire;

use App\Models\PurchaseOrder;
use App\Models\DeliveryOrder;
use App\Models\Item;
use App\Models\IBCChemical;
use App\Models\IncomingQualityControl;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;

class Dashboard extends Component
{
    public $timeframe = 'today';
    public $chartData;
    public $startDate;
    public $endDate;
    public $interval;
    public $format;

    public function mount()
    {
        $this->updateTimeframe('today');
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

    public function getExpiringChemicals()
    {
        $now = Carbon::today();
        $in7 = Carbon::today()->addDays(7);

        $ibc = IBCChemical::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>', $now)
            ->whereDate('expiry_date', '<=', $in7)
            ->get(['do_num', 'che_code', 'expiry_date']);

        $iqc = IncomingQualityControl::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>', $now)
            ->whereDate('expiry_date', '<=', $in7)
            ->get(['do_num', 'che_code', 'expiry_date']);

        // Merge and add days_left
        $all = $ibc->map(function($row) use ($now) {
            $row->type = 'IBC';
            $row->days_left = Carbon::parse($row->expiry_date)->diffInDays($now);
            return $row;
        })->concat(
            $iqc->map(function($row) use ($now) {
                $row->type = 'iQC';
                $row->days_left = Carbon::parse($row->expiry_date)->diffInDays($now);
                return $row;
            })
        );
        return $all->sortBy('days_left')->values();
    }

    public function render()
    {
        $totals = $this->getTotalStats();
        $inventory = $this->getInventoryStats();
        $expiringChemicals = $this->getExpiringChemicals();
        return view('livewire.dashboard', compact('totals', 'inventory', 'expiringChemicals'))->layout('layouts.app');
    }
}
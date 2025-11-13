<?php

namespace App\Livewire\ChemicalForm;

use App\Models\IBCChemical;
use App\Models\LoadingUnloading;
use App\Models\IncomingQualityControl;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsumptionDashboard extends Component
{
    public $currentMonth;
    public $comparisonMonth;
    public $ibcData = [];
    public $loadingUnloadingData = [];
    public $incomingQCData = [];
    public $selectedComparisonMonth;
    public $availableMonths = [];

    public function mount()
    {
        // Get all available months from the database
        $this->availableMonths = $this->getAvailableMonths();
        
        if ($this->availableMonths->isEmpty()) {
            // If no data is available, set current month to current month
            $this->currentMonth = Carbon::now();
            $this->selectedComparisonMonth = Carbon::now()->subMonth()->format('Y-m');
        } else {
            // Set current month to the latest available month
            $this->currentMonth = Carbon::createFromFormat('Y-m', $this->availableMonths->first()['value']);
            // Set default comparison month to previous month
            $this->selectedComparisonMonth = Carbon::now()->subMonth()->format('Y-m');
        }
        
        $this->updateMonths();
    }

    public function updatedSelectedComparisonMonth()
    {
        $this->updateMonths();
    }

    protected function updateMonths()
    {
        $this->comparisonMonth = Carbon::createFromFormat('Y-m', $this->selectedComparisonMonth);
        $this->loadData();
    }

    protected function getAvailableMonths()
    {
        $months = collect();
        
        // Get months from IBC Chemical
        $ibcMonths = IBCChemical::select(DB::raw('DISTINCT DATE_FORMAT(date, "%Y-%m") as month'))
            ->orderBy('month', 'desc')
            ->pluck('month');
            
        // Get months from Loading/Unloading
        $loadingMonths = LoadingUnloading::select(DB::raw('DISTINCT DATE_FORMAT(date, "%Y-%m") as month'))
            ->orderBy('month', 'desc')
            ->pluck('month');
            
        // Get months from Incoming QC
        $qcMonths = IncomingQualityControl::select(DB::raw('DISTINCT DATE_FORMAT(date_arrived, "%Y-%m") as month'))
            ->orderBy('month', 'desc')
            ->pluck('month');
            
        // Combine all months and get unique values
        return $ibcMonths->concat($loadingMonths)->concat($qcMonths)
            ->unique()
            ->sort()
            ->reverse()
            ->values()
            ->map(function ($month) {
                return [
                    'value' => $month,
                    'label' => Carbon::createFromFormat('Y-m', $month)->format('F Y')
                ];
            });
    }

    public function loadData()
    {
        // Load IBC Chemical Data
        $this->ibcData = IBCChemical::select('che_code', 
            DB::raw('SUM(CASE WHEN MONTH(date) = ? AND YEAR(date) = ? THEN qty ELSE 0 END) as current_month_qty'),
            DB::raw('SUM(CASE WHEN MONTH(date) = ? AND YEAR(date) = ? THEN qty ELSE 0 END) as comparison_month_qty'))
            ->setBindings([
                $this->currentMonth->month, $this->currentMonth->year,
                $this->comparisonMonth->month, $this->comparisonMonth->year
            ])
            ->groupBy('che_code')
            ->get()
            ->map(function ($item) {
                $item->difference = $item->current_month_qty - $item->comparison_month_qty;
                return $item;
            });

        // Load Loading/Unloading Data
        $this->loadingUnloadingData = LoadingUnloading::select('che_code',
            DB::raw('AVG(CASE WHEN MONTH(date) = ? AND YEAR(date) = ? THEN che_after ELSE 0 END) as current_month_percentage'),
            DB::raw('AVG(CASE WHEN MONTH(date) = ? AND YEAR(date) = ? THEN che_after ELSE 0 END) as comparison_month_percentage'))
            ->setBindings([
                $this->currentMonth->month, $this->currentMonth->year,
                $this->comparisonMonth->month, $this->comparisonMonth->year
            ])
            ->groupBy('che_code')
            ->get()
            ->map(function ($item) {
                $item->difference = $item->current_month_percentage - $item->comparison_month_percentage;
                return $item;
            });

        // Load Incoming QC Data
        $this->incomingQCData = IncomingQualityControl::select('che_code',
            DB::raw('SUM(CASE WHEN MONTH(date_arrived) = ? AND YEAR(date_arrived) = ? THEN qty ELSE 0 END) as current_month_qty'),
            DB::raw('SUM(CASE WHEN MONTH(date_arrived) = ? AND YEAR(date_arrived) = ? THEN qty ELSE 0 END) as comparison_month_qty'))
            ->setBindings([
                $this->currentMonth->month, $this->currentMonth->year,
                $this->comparisonMonth->month, $this->comparisonMonth->year
            ])
            ->groupBy('che_code')
            ->get()
            ->map(function ($item) {
                $item->difference = $item->current_month_qty - $item->comparison_month_qty;
                return $item;
            });
    }

    public function render()
    {
        return view('livewire.chemical-form.consumption-dashboard', [
            'currentMonthName' => $this->currentMonth->format('F Y'),
            'comparisonMonthName' => $this->comparisonMonth->format('F Y')
        ])->layout('layouts.app');
    }
}

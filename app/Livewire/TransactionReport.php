<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\CompanyProfile;
use App\Models\Family;
use App\Models\Category;
use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class TransactionReport extends Component
{
    public $fileType = 'pdf';
    public $selectedColumns = [];
    public $isGenerating = false;
    public $errorMessage = '';
    public $startDate;
    public $endDate;
    public $selectedTransactionType = 'all';
    public $stockFilter = 'all'; // all | gt0 | eq0
    public $selectedGroupId = null;
    public $selectedFamilyId = null;
    public $selectedCategoryId = null;
    
    public $availableColumns = [
        'created_at' => 'Transaction Time',
        'item_code' => 'Item Code',
        'item_name' => 'Item Name',
        'qty_on_hand' => 'Quantity on Hand',
        'qty_before' => 'Transaction Quantity Before',
        'qty_after' => 'Transaction Quantity After',
        'transaction_qty' => 'Transaction Quantity',
        'transaction_type' => 'Transaction Type',
        'source_type' => 'Source Type',
        'source_doc_num' => 'Source Document Number',
        'username' => 'User',
        'batch_num' => 'Batch Number'
    ];

    public $transactionTypes = [
        'all' => 'All Transactions',
        'Stock In' => 'Stock In',
        'Stock Out' => 'Stock Out'
    ];

    protected $rules = [
        'fileType' => 'required|in:pdf,excel',
        'selectedColumns' => 'required|array|min:1',
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'selectedTransactionType' => 'required|in:all,Stock In,Stock Out',
        'stockFilter' => 'required|in:all,gt0,eq0'
    ];

    public function mount()
    {
        $this->selectedColumns = ['item_code', 'item_name', 'created_at', 'qty_on_hand', 'transaction_type', 'transaction_qty'];
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->stockFilter = 'all';
        $this->selectedGroupId = null;
        $this->selectedFamilyId = null;
        $this->selectedCategoryId = null;
    }

    public function generateReport()
    {
        try {
            $this->validate();
            $this->isGenerating = true;
            $this->errorMessage = '';
    
            // Get all transactions in the date range
            $transactionQuery = Transaction::query();
            
            // Apply date filters
            if ($this->startDate) {
                $transactionQuery->whereDate('transactions.created_at', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $transactionQuery->whereDate('transactions.created_at', '<=', $this->endDate);
            }

            // Apply transaction type filter
            if ($this->selectedTransactionType !== 'all') {
                $transactionQuery->where('transactions.transaction_type', $this->selectedTransactionType);
            }
    
            // Join with items and apply item filters
            $transactionQuery->leftJoin('items', 'transactions.item_id', '=', 'items.id')
                  ->leftJoin('categories', 'items.cat_id', '=', 'categories.id')
                  ->leftJoin('families', 'items.family_id', '=', 'families.id')
                  ->leftJoin('groups', 'items.group_id', '=', 'groups.id');

            // Apply Group filter
            if ($this->selectedGroupId) {
                $transactionQuery->where('items.group_id', '=', $this->selectedGroupId);
            }

            // Apply Family filter
            if ($this->selectedFamilyId) {
                $transactionQuery->where('items.family_id', '=', $this->selectedFamilyId);
            }

            // Apply Category filter
            if ($this->selectedCategoryId) {
                $transactionQuery->where('items.cat_id', '=', $this->selectedCategoryId);
            }

            // Get all transactions
            $transactions = $transactionQuery->select([
                'transactions.item_id',
                'transactions.qty_before',
                'transactions.qty_after',
                'transactions.transaction_qty',
                'transactions.transaction_type',
                'transactions.created_at',
                'items.item_code',
                'items.item_name',
                'items.um',
                'items.group_id',
                'items.family_id',
                'items.cat_id',
                'groups.group_name',
                'families.family_name',
                'categories.cat_name'
            ])->orderBy('transactions.created_at', 'asc')->get();

            if ($transactions->isEmpty()) {
                throw new \Exception('No data available for the selected date range and filters.');
            }

            // Group transactions by item and calculate B/F, IN, OUT, BALANCE
            $itemBalances = [];
            $itemFirstTransaction = []; // Track first transaction per item for B/F
            
            foreach ($transactions as $transaction) {
                $itemId = $transaction->item_id;
                
                if (!isset($itemBalances[$itemId])) {
                    // Initialize item balance - B/F is the qty_before of the first transaction
                    $itemBalances[$itemId] = [
                        'item_id' => $itemId,
                        'item_code' => $transaction->item_code,
                        'item_name' => $transaction->item_name,
                        'um' => $transaction->um ?? 'PCS',
                        'group_name' => $transaction->group_name ?? '',
                        'family_name' => $transaction->family_name ?? '',
                        'cat_name' => $transaction->cat_name ?? '',
                        'bf' => $transaction->qty_before ?? 0, // Balance Forward (first transaction's qty_before)
                        'in' => 0,
                        'out' => 0,
                        'balance' => 0
                    ];
                    $itemFirstTransaction[$itemId] = true;
                }
                
                // Calculate IN and OUT
                if ($transaction->transaction_type === 'Stock In') {
                    $itemBalances[$itemId]['in'] += abs($transaction->transaction_qty ?? 0);
                } elseif ($transaction->transaction_type === 'Stock Out') {
                    $itemBalances[$itemId]['out'] += abs($transaction->transaction_qty ?? 0);
                }
            }
            
            // Calculate final balance for each item
            foreach ($itemBalances as &$balance) {
                $balance['balance'] = $balance['bf'] + $balance['in'] - $balance['out'];
            }
            
            // Convert to collection and sort
            $stockBalances = collect($itemBalances)->values();
            
            // Apply stock filter
            if ($this->stockFilter === 'gt0') {
                $stockBalances = $stockBalances->filter(function($item) {
                    return $item['balance'] > 0;
                });
            } elseif ($this->stockFilter === 'eq0') {
                $stockBalances = $stockBalances->filter(function($item) {
                    return $item['balance'] == 0;
                });
            }
            
            // Sort by Group, Family, Category, Item Code
            $stockBalances = $stockBalances->sortBy([
                ['group_name', 'asc'],
                ['family_name', 'asc'],
                ['cat_name', 'asc'],
                ['item_code', 'asc']
            ])->values();
    
            if ($stockBalances->isEmpty()) {
                throw new \Exception('No data available for the selected filters.');
            }
    
            $response = $this->fileType === 'pdf'
                ? $this->downloadPDF($stockBalances)
                : $this->downloadExcel($stockBalances);
    
            $this->isGenerating = false;
            return $response;
    
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate report: ' . $e->getMessage();
            $this->isGenerating = false;
            return null;
        }
    }

    protected function downloadPDF($stockBalances)
    {
        try {
            $companyProfile = CompanyProfile::first();
            
            // Get filter names
            $groupName = $this->selectedGroupId ? Group::find($this->selectedGroupId)->group_name ?? 'ALL' : 'ALL';
            $familyName = $this->selectedFamilyId ? Family::find($this->selectedFamilyId)->family_name ?? 'ALL' : 'ALL';
            $categoryName = $this->selectedCategoryId ? Category::find($this->selectedCategoryId)->cat_name ?? 'ALL' : 'ALL';
            $stockFilterName = $this->stockFilter === 'gt0' ? '> 0' : ($this->stockFilter === 'eq0' ? '= 0' : 'ALL');
            
            $pdf = PDF::loadView('reports.transactions', [
                'stockBalances' => $stockBalances,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'companyProfile' => $companyProfile,
                'groupName' => $groupName,
                'familyName' => $familyName,
                'categoryName' => $categoryName,
                'stockFilter' => $stockFilterName
            ])->setPaper('a4', 'portrait');

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'stock_balance_report_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            throw new \Exception('PDF generation failed: ' . $e->getMessage());
        }
    }

    protected function downloadExcel($transactions)
    {
        try {
            return Excel::download(
                new TransactionsExport($transactions, $this->selectedColumns), 
                'inventory_report_' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            throw new \Exception('Excel generation failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $groups = Group::orderBy('group_name')->get();
        $families = Family::orderBy('family_name')->get();
        $categories = Category::orderBy('cat_name')->get();
        
        return view('livewire.transaction-report', [
            'groups' => $groups,
            'families' => $families,
            'categories' => $categories
        ])->layout('layouts.app');
    }
}
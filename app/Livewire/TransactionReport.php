<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Response;

class TransactionReport extends Component
{
    public $fileType = 'pdf';
    public $selectedColumns = [];
    public $isGenerating = false;
    public $errorMessage = '';
    public $startDate;
    public $endDate;
    public $selectedTransactionType = 'all';
    
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
        'selectedTransactionType' => 'required|in:all,Stock In,Stock Out'
    ];

    public function mount()
    {
        $this->selectedColumns = ['item_code', 'item_name', 'created_at', 'qty_on_hand', 'transaction_type', 'transaction_qty'];
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function generateReport()
    {
        try {
            $this->validate();
            $this->isGenerating = true;
            $this->errorMessage = '';
    
            $finalColumns = array_unique(array_merge(
                ['item_code', 'item_name', 'qty_on_hand', 'transaction_qty'],
                $this->selectedColumns
            ));
    
            $query = Transaction::select([
                'transactions.created_at',
                'transactions.qty_on_hand',
                'transactions.transaction_type',
                'transactions.qty_before',
                'transactions.qty_after',
                'transactions.transaction_qty',
                'transactions.source_doc_num',
                'transactions.source_type',
            ])->orderBy('transactions.created_at', 'desc');
            
            // Apply date filters
            if ($this->startDate) {
                $query->whereDate('transactions.created_at', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $query->whereDate('transactions.created_at', '<=', $this->endDate);
            }

            // Apply transaction type filter
            if ($this->selectedTransactionType !== 'all') {
                $query->where('transactions.transaction_type', $this->selectedTransactionType);
            }
    
            // Add additional fields if selected
            if (in_array('item_code', $finalColumns)) {
                $query->addSelect('items.item_code');
            }
            if (in_array('item_name', $finalColumns)) {
                $query->addSelect('items.item_name');
            }
            if (in_array('batch_num', $finalColumns)) {
                $query->addSelect('batch_trackings.batch_num');
            }
            if (in_array('username', $finalColumns)) {
                $query->addSelect('users.username');
            }
    
            // Join tables correctly
            $query->leftJoin('items', 'transactions.item_id', '=', 'items.id')
                  ->leftJoin('batch_trackings', 'transactions.batch_id', '=', 'batch_trackings.id')
                  ->leftJoin('users', 'transactions.user_id', '=', 'users.id');
    
            $items = $query->get();
    
            if ($items->isEmpty()) {
                throw new \Exception('No data available for the selected date range and transaction type.');
            }

            // No need to calculate transaction quantity as it's already stored in the database
            // The transaction_qty field is already populated with the correct value
    
            $response = $this->fileType === 'pdf'
                ? $this->downloadPDF($items)
                : $this->downloadExcel($items);
    
            $this->isGenerating = false;
            return $response;
    
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate report: ' . $e->getMessage();
            $this->isGenerating = false;
            return null;
        }
    }

    protected function downloadPDF($transactions)
    {
        try {
            $pdf = PDF::loadView('reports.transactions', [
                'transactions' => $transactions,
                'columns' => array_intersect_key($this->availableColumns, array_flip($this->selectedColumns)),
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'transactionType' => $this->selectedTransactionType
            ]);

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'transaction_report_' . date('Y-m-d') . '.pdf');
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
        return view('livewire.transaction-report')->layout('layouts.app');
    }
}
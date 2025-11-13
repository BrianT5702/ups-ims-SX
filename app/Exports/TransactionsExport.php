<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $transactions;
    protected $columns;

    public function __construct($transactions, array $columns)
    {
        $this->transactions = $transactions;
        $this->columns = $columns;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        $headers = [];
        foreach ($this->columns as $column) {
            $headers[] = ucwords(str_replace('_', ' ', $column));
        }
        return $headers;
    }

    public function map($transaction): array
    {
        $row = [];
        foreach ($this->columns as $column) {
            $row[] = $transaction->{$column};
        }
        return $row;
    }
}

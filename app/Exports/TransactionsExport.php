<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $transactions;
    protected $columns;
    protected array $columnLabels;
    protected bool $includeRowNumber;
    protected int $rowNumber = 0;

    public function __construct($transactions, array $columns, array $columnLabels = [], bool $includeRowNumber = false)
    {
        $this->transactions = $transactions;
        $this->columns = $columns;
        $this->columnLabels = $columnLabels;
        $this->includeRowNumber = $includeRowNumber;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        $headers = [];

        if ($this->includeRowNumber) {
            $headers[] = 'NO';
        }

        foreach ($this->columns as $column) {
            $headers[] = $this->columnLabels[$column] ?? ucwords(str_replace('_', ' ', $column));
        }
        return $headers;
    }

    public function map($transaction): array
    {
        $row = [];

        if ($this->includeRowNumber) {
            $this->rowNumber++;
            $row[] = $this->rowNumber;
        }

        foreach ($this->columns as $column) {
            if (is_array($transaction)) {
                $row[] = $transaction[$column] ?? null;
                continue;
            }

            $row[] = $transaction->{$column} ?? null;
        }
        return $row;
    }
}

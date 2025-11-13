<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $items;
    protected $columns;

    public function __construct($items, array $columns)
    {
        $this->items = $items;
        $this->columns = $columns;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        $headers = [];
        foreach ($this->columns as $column) {
            $headers[] = ucwords(str_replace('_', ' ', $column));
        }
        return $headers;
    }

    public function map($item): array
    {
        $row = [];
        foreach ($this->columns as $column) {
            $row[] = $item->{$column};
        }
        return $row;
    }
}
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Collection;
use App\Models\CompanyProfile;
use Carbon\Carbon;

class ItemsExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithCustomStartCell, WithColumnFormatting
{
    protected $items;
    protected $columns;
    protected $columnLabels;
    protected $useGrouping;

    public function __construct($items, array $columns, array $columnLabels = [], $useGrouping = true)
    {
        $this->items = $items;
        $this->columns = $columns;
        $this->columnLabels = $columnLabels;
        $this->useGrouping = $useGrouping;
    }

    public function startCell(): string
    {
        return 'A5'; // Start data after header rows
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        $headers = [];
        foreach ($this->columns as $column) {
            // Use provided label if available, otherwise format the column name
            $headers[] = $this->columnLabels[$column] ?? ucwords(str_replace('_', ' ', $column));
        }
        return $headers;
    }

    public function map($item): array
    {
        $row = [];
        foreach ($this->columns as $column) {
            $value = $item->{$column} ?? '';
            
            // Format numbers
            if (in_array($column, ['cost', 'cash_price', 'term_price', 'cust_price']) && $value) {
                $value = number_format($value, 2);
            }
            
            // Handle quantity: show '0' if qty is 0, otherwise show value or empty
            if ($column === 'qty') {
                $value = $item->qty === 0 ? '0' : ($item->qty ?: '');
            }
            
            $row[] = $value;
        }
        return $row;
    }

    public function columnFormats(): array
    {
        $formats = [];
        $colIndex = 1; // Start from column A
        
        foreach ($this->columns as $column) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            
            // Format numeric columns
            if (in_array($column, ['cost', 'cash_price', 'term_price', 'cust_price'])) {
                $formats[$colLetter] = '#,##0.00';
            } elseif ($column === 'qty') {
                $formats[$colLetter] = '0';
            }
            
            $colIndex++;
        }
        
        return $formats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $companyProfile = CompanyProfile::first();
                $companyName = $companyProfile->company_name ?? 'UNITED REFRIGERATION SYSTEM (M) SDN BHD';
                
                // Use Asia/Kuala_Lumpur timezone
                $now = Carbon::now('Asia/Kuala_Lumpur');
                $date = $now->format('d/m/Y');
                $time = $now->format('H:i:s');
                
                // Add header rows - match PDF/HTML layout
                $lastCol = Coordinate::stringFromColumnIndex(count($this->columns));
                
                // Row 1: Company name (left) and Date/Time (right)
                $sheet->setCellValue('A1', $companyName);
                $sheet->setCellValue($lastCol . '1', 'DATE : ' . $date . "\n" . 'TIME : ' . $time);
                $sheet->mergeCells('A1:' . Coordinate::stringFromColumnIndex(count($this->columns) - 1) . '1');
                
                // Row 4: STOCK LISTING (centered)
                $sheet->setCellValue('A4', 'STOCK LISTING');
                $sheet->mergeCells('A4:' . $lastCol . '4');
                
                // Style header rows
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                $sheet->getStyle($lastCol . '1')->applyFromArray([
                    'font' => ['size' => 8],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_TOP],
                ]);
                
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                // Style column headers (row 5)
                $headerRange = 'A5:' . $lastCol . '5';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F0F0'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Add grouping rows if enabled
                if ($this->useGrouping) {
                    $this->addGroupingRows($sheet, $event);
                }
                
                // Auto-size columns
                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
    
    protected function addGroupingRows($sheet, AfterSheet $event)
    {
        if (!$this->useGrouping) {
            return;
        }
        
        $row = 6; // Start after header row (row 5)
        $prevGroup = '';
        $prevBrand = '';
        $prevType = '';
        $lastCol = Coordinate::stringFromColumnIndex(count($this->columns));
        $itemsArray = $this->items->values()->all();
        
        foreach ($itemsArray as $index => $item) {
            $groupName = trim($item->group_name ?? '');
            $brandName = trim($item->family_name ?? '');
            $typeName = trim($item->cat_name ?? '');
            // Treat "UNDEFINED" as empty
            if (strtoupper($typeName) === 'UNDEFINED') {
                $typeName = '';
            }
            
            $showGroup = $groupName !== $prevGroup;
            $showBrand = $showGroup || ($brandName !== $prevBrand);
            $showType = $showGroup || $showBrand || ($typeName !== $prevType);
            
            if ($showGroup || $showBrand || $showType) {
                // Insert grouping row before current data row
                $sheet->insertNewRowBefore($row, 1);
                
                // Set grouping text in first cell and merge - match PDF/HTML format
                // Always show "TYPE: " even if empty or UNDEFINED
                $groupText = 'GROUP: ' . $groupName . ' | BRAND: ' . $brandName . ' | TYPE: ' . $typeName;
                $sheet->setCellValue('A' . $row, $groupText);
                $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
                
                // Style grouping row
                $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E0E0E0'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Try to align text - left for GROUP, center for BRAND, right for TYPE
                // Since we can't easily split in Excel, we'll use a workaround with custom text
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                $row++; // Move to next row (data row)
            }
            
            // Check if next item is different group (need blank line after this item)
            $nextItem = $itemsArray[$index + 1] ?? null;
            if ($nextItem) {
                $nextGroup = trim($nextItem->group_name ?? '');
                $nextBrand = trim($nextItem->family_name ?? '');
                $nextType = trim($nextItem->cat_name ?? '');
                // Treat "UNDEFINED" as empty for comparison
                if (strtoupper($nextType) === 'UNDEFINED') {
                    $nextType = '';
                }
                $needsBlankLine = ($nextGroup !== $groupName) || ($nextBrand !== $brandName) || ($nextType !== $typeName);
                
                if ($needsBlankLine) {
                    // Insert blank row after current item (before next group)
                    $sheet->insertNewRowBefore($row + 1, 1);
                    $row++; // Increment to account for blank row
                }
            }
            
            $prevGroup = $groupName;
            $prevBrand = $brandName;
            $prevType = $typeName;
            $row++; // Move to next item
        }
    }
}
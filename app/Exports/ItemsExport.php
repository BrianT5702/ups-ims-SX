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
    protected $showTotals;

    public function __construct($items, array $columns, array $columnLabels = [], $useGrouping = true, $showTotals = false)
    {
        $this->items = $items;
        $this->columns = $columns;
        $this->columnLabels = $columnLabels;
        $this->useGrouping = $useGrouping;
        $this->showTotals = $showTotals;
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
        // Add Amount column if totals are enabled
        if ($this->showTotals) {
            $headers[] = 'Amount';
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
        
        // Add subtotal if totals are enabled
        if ($this->showTotals) {
            $qty = $item->qty ?? 0;
            $cost = $item->cost ?? 0;
            $subtotal = $qty * $cost;
            $row[] = number_format($subtotal, 2);
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
        
        // Format Amount column if totals are enabled
        if ($this->showTotals) {
            $amountCol = Coordinate::stringFromColumnIndex($colIndex);
            $formats[$amountCol] = '#,##0.00';
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
                $columnCount = count($this->columns) + ($this->showTotals ? 1 : 0);
                $lastCol = Coordinate::stringFromColumnIndex($columnCount);
                
                // Row 1: Company name (left) and Date/Time (right)
                $sheet->setCellValue('A1', $companyName);
                $sheet->setCellValue($lastCol . '1', 'DATE : ' . $date . "\n" . 'TIME : ' . $time);
                $sheet->mergeCells('A1:' . Coordinate::stringFromColumnIndex(count($this->columns) - 1) . '1');
                
                // Row 4: STOCK LISTING (centered)
                $sheet->setCellValue('A4', 'STOCK LISTING');
                $sheet->mergeCells('A4:' . $lastCol . '4');
                
                // Style header rows
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                $sheet->getStyle($lastCol . '1')->applyFromArray([
                    'font' => ['size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_TOP],
                ]);
                
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                // Style column headers (row 5)
                $headerRange = 'A5:' . $lastCol . '5';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
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
                
                // Set font size for all data rows (starting from row 6)
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 6) {
                    $dataRange = 'A6:' . $lastCol . $highestRow;
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'font' => ['size' => 10],
                    ]);
                }
                
                // Add grand total row if totals are enabled
                if ($this->showTotals) {
                    $highestRow = $sheet->getHighestRow();
                    $totalRow = $highestRow + 1;
                    
                    // Calculate grand total
                    $grandTotal = 0;
                    foreach ($this->items as $item) {
                        $qty = $item->qty ?? 0;
                        $cost = $item->cost ?? 0;
                        $grandTotal += ($qty * $cost);
                    }
                    
                    // Set grand total label (second to last column)
                    $grandTotalLabelCol = Coordinate::stringFromColumnIndex($columnCount - 1);
                    $grandTotalCol = $lastCol;
                    
                    $sheet->setCellValue($grandTotalLabelCol . $totalRow, 'Grand Total:');
                    $sheet->setCellValue($grandTotalCol . $totalRow, number_format($grandTotal, 2));
                    
                    // Style grand total row
                    $sheet->getStyle($grandTotalLabelCol . $totalRow . ':' . $grandTotalCol . $totalRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'borders' => [
                            'top' => [
                                'borderStyle' => Border::BORDER_THICK,
                            ],
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                    
                    $sheet->getStyle($grandTotalLabelCol . $totalRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    ]);
                    
                    $sheet->getStyle($grandTotalCol . $totalRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    ]);
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
        $columnCount = count($this->columns) + ($this->showTotals ? 1 : 0);
        $lastCol = Coordinate::stringFromColumnIndex($columnCount);
        $itemsArray = $this->items->values()->all();
        $currentGroupKey = '';
        $groupSubtotal = 0;
        $groupStartRow = $row;
        
        foreach ($itemsArray as $index => $item) {
            $groupName = trim($item->group_name ?? '');
            $brandName = trim($item->family_name ?? '');
            $typeName = trim($item->cat_name ?? '');
            // Treat "UNDEFINED" as empty
            if (strtoupper($typeName) === 'UNDEFINED') {
                $typeName = '';
            }
            
            $currentKey = $groupName . '|' . $brandName . '|' . $typeName;
            $showGroup = $groupName !== $prevGroup;
            $showBrand = $showGroup || ($brandName !== $prevBrand);
            $showType = $showGroup || $showBrand || ($typeName !== $prevType);
            $isNewGroup = ($currentKey !== $currentGroupKey);
            
            // Check if next item is different group
            $nextItem = $itemsArray[$index + 1] ?? null;
            $isLastInGroup = false;
            if ($nextItem) {
                $nextGroup = trim($nextItem->group_name ?? '');
                $nextBrand = trim($nextItem->family_name ?? '');
                $nextType = trim($nextItem->cat_name ?? '');
                if (strtoupper($nextType) === 'UNDEFINED') {
                    $nextType = '';
                }
                $nextKey = $nextGroup . '|' . $nextBrand . '|' . $nextType;
                $isLastInGroup = ($nextKey !== $currentKey);
            } else {
                $isLastInGroup = true; // Last item overall
            }
            
            // Update group tracking - reset subtotal when starting new group
            if ($isNewGroup) {
                // Reset subtotal for the new group (previous group's subtotal was already shown when it ended)
                if ($currentGroupKey !== '') {
                    $groupSubtotal = 0; // Reset for new group
                }
                $currentGroupKey = $currentKey;
            }
            
            if ($showGroup || $showBrand || $showType) {
                // Insert grouping row before current data row
                $sheet->insertNewRowBefore($row, 1);
                
                // Set grouping text in first cell and merge - match PDF/HTML format
                $groupText = 'GROUP: ' . $groupName . ' | BRAND: ' . $brandName . ' | TYPE: ' . $typeName;
                $sheet->setCellValue('A' . $row, $groupText);
                $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
                
                // Style grouping row
                $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
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
                
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                $row++; // Move to next row (data row)
            }
            
            // Calculate and accumulate subtotal
            if ($this->showTotals) {
                $qty = $item->qty ?? 0;
                $cost = $item->cost ?? 0;
                $groupSubtotal += ($qty * $cost);
            }
            
            // If last item in group, show subtotal
            if ($isLastInGroup && $this->showTotals) {
                $row++; // Move to next row after current item
                $sheet->insertNewRowBefore($row, 1);
                
                // Save the subtotal before resetting
                $finalGroupSubtotal = $groupSubtotal;
                $groupSubtotal = 0; // Reset for next group
                
                $subtotalLabelCol = Coordinate::stringFromColumnIndex($columnCount - 1);
                $subtotalCol = $lastCol;
                
                $sheet->setCellValue($subtotalLabelCol . $row, 'Sub Total:');
                $sheet->setCellValue($subtotalCol . $row, number_format($finalGroupSubtotal, 2));
                
                $sheet->getStyle($subtotalLabelCol . $row . ':' . $subtotalCol . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                $sheet->getStyle($subtotalLabelCol . $row)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                
                $sheet->getStyle($subtotalCol . $row)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                
                $row++;
            }
            
            // Insert blank line if needed
            if ($isLastInGroup && $nextItem) {
                $sheet->insertNewRowBefore($row, 1);
                $row++;
            }
            
            $prevGroup = $groupName;
            $prevBrand = $brandName;
            $prevType = $typeName;
            
            if (!$isLastInGroup) {
                $row++; // Move to next item (unless we already moved for subtotal)
            }
        }
    }
}
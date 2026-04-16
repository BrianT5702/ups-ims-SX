<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QuotationNumberService
{
    /**
     * Preview the next quotation number without consuming it (for display only).
     */
    public static function getNextQuotationNumberPreview(string $connection): string
    {
        $prefix = config('quotation.prefix', 'QT');
        $padLength = config('quotation.pad_length', 5);
        $start = config("quotation.start.{$connection}", 1);

        $row = DB::connection($connection)->table('quotation_number_sequences')->first();
        $nextNumber = $row ? (int) $row->next_number : $start;

        return $prefix . str_pad((string) $nextNumber, $padLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get the next sequential quotation number for the given database connection.
     *
     * @param  bool  $withinTransaction  If true, run without own transaction (caller must be in transaction).
     */
    public static function getNextQuotationNumber(string $connection, bool $withinTransaction = false): string
    {
        $prefix = config('quotation.prefix', 'QT');
        $padLength = config('quotation.pad_length', 5);
        $start = config("quotation.start.{$connection}", 1);

        $increment = function () use ($connection, $prefix, $padLength, $start) {
            $row = DB::connection($connection)
                ->table('quotation_number_sequences')
                ->lockForUpdate()
                ->first();

            if (!$row) {
                $nextNumber = $start;
                DB::connection($connection)->table('quotation_number_sequences')->insert([
                    'next_number' => $nextNumber + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $nextNumber = (int) $row->next_number;
                DB::connection($connection)
                    ->table('quotation_number_sequences')
                    ->where('id', $row->id)
                    ->update([
                        'next_number' => $nextNumber + 1,
                        'updated_at' => now(),
                    ]);
            }

            return $prefix . str_pad((string) $nextNumber, $padLength, '0', STR_PAD_LEFT);
        };

        if ($withinTransaction) {
            return $increment();
        }

        return DB::connection($connection)->transaction($increment);
    }
}

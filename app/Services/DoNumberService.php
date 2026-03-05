<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DoNumberService
{
    /**
     * Preview the next DO number without consuming it (for display only).
     */
    public static function getNextDoNumberPreview(string $connection): string
    {
        $prefix = config('do.prefix', 'DO');
        $padLength = config('do.pad_length', 6);
        $start = config("do.start.{$connection}", 1);

        $row = DB::connection($connection)->table('do_number_sequences')->first();
        $nextNumber = $row ? (int) $row->next_number : $start;

        return $prefix . str_pad((string) $nextNumber, $padLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get the next sequential DO number for the given database connection.
     * Uses atomic increment to prevent duplicates.
     */
    public static function getNextDoNumber(string $connection): string
    {
        $prefix = config('do.prefix', 'DO');
        $padLength = config('do.pad_length', 6);
        $start = config("do.start.{$connection}", 1);

        return DB::connection($connection)->transaction(function () use ($connection, $prefix, $padLength, $start) {
            $row = DB::connection($connection)
                ->table('do_number_sequences')
                ->lockForUpdate()
                ->first();

            if (!$row) {
                $nextNumber = $start;
                DB::connection($connection)->table('do_number_sequences')->insert([
                    'next_number' => $nextNumber + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $nextNumber = (int) $row->next_number;
                DB::connection($connection)
                    ->table('do_number_sequences')
                    ->where('id', $row->id)
                    ->update([
                        'next_number' => $nextNumber + 1,
                        'updated_at' => now(),
                    ]);
            }

            return $prefix . str_pad((string) $nextNumber, $padLength, '0', STR_PAD_LEFT);
        });
    }
}

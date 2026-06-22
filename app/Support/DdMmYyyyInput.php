<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class DdMmYyyyInput
{
    /**
     * Parse dd/mm/yyyy, ddmmyyyy, or yyyy-mm-dd into Y-m-d for queries.
     */
    public static function toIso(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            try {
                $parsed = Carbon::createFromFormat('!d/m/Y', $value);

                return $parsed->format('d/m/Y') === $value
                    ? $parsed->format('Y-m-d')
                    : null;
            } catch (InvalidFormatException) {
                return null;
            }
        }

        if (preg_match('/^\d{8}$/', $value)) {
            try {
                $parsed = Carbon::createFromFormat('!dmY', $value);

                return $parsed->format('dmY') === $value
                    ? $parsed->format('Y-m-d')
                    : null;
            } catch (InvalidFormatException) {
                return null;
            }
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            } catch (InvalidFormatException) {
                return null;
            }
        }

        return null;
    }

    public static function toDisplay(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $iso = self::toIso($value);

        if (!$iso) {
            return trim($value);
        }

        return Carbon::createFromFormat('Y-m-d', $iso)->format('d/m/Y');
    }
}

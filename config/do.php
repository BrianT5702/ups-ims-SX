<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DO Number Start (per company/database)
    |--------------------------------------------------------------------------
    | Starting number for sequential DO numbering. Format: DO + padded number.
    | Each company has its own sequence (separate databases).
    */
    'start' => [
        'ups' => 49037,   // 049037
        'urs' => 1,       // 000001
        'ucs' => 1,       // 000001
        'ups2' => 1,
        'urs2' => 30398,  // 030398 — adjust via: php artisan do:set-next-number urs2 {n}
        'ucs2' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | DO Number Format
    |--------------------------------------------------------------------------
    */
    'prefix' => '',  // Empty = numbers only (e.g. 049037), use 'DO' for DO049037
    'pad_length' => 6,

    /*
    |--------------------------------------------------------------------------
    | Description wrap limit (one-page row estimate)
    |--------------------------------------------------------------------------
    | UTF-8 byte length per visual line (strlen) when counting description rows.
    */
    'description_chars_per_row' => 78,
];

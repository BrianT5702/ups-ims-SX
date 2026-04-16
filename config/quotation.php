<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Quotation number start (per company/database)
    |--------------------------------------------------------------------------
    | Each company DB has its own sequence. Format: prefix + padded number
    | (e.g. UPS: QT00518).
    */
    'start' => [
        'ups' => 518, // QT00518
        'urs' => 1,
        'ucs' => 1,
    ],

    'prefix' => 'QT',
    'pad_length' => 5,
];

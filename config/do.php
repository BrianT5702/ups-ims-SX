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
        'ups' => 49037,  // DO049037
        'urs' => 1,      // DO000001
        'ucs' => 1,      // DO000001
    ],

    /*
    |--------------------------------------------------------------------------
    | DO Number Format
    |--------------------------------------------------------------------------
    */
    'prefix' => 'DO',
    'pad_length' => 6,
];

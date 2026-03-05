<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DO Number Format
    |--------------------------------------------------------------------------
    |
    | Prefix and numbering for Delivery Orders. Per-tenant start_number allows
    | each company (UPS, URS, UCS) to have its own starting DO sequence.
    | The numeric part is zero-padded to pad_length digits.
    |
    */

    'prefix' => 'DO',

    'pad_length' => 6,

    /**
     * Per-tenant start number. Only UPS uses 049037. Other tenants use 1.
     * Add entries for urs, ucs, etc. as needed.
     */
    'tenants' => [
        'ups' => 49037,
        'urs' => 1,
        'ucs' => 1,
    ],

    'default_start_number' => 1,

];

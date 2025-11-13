<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class IBCChemical extends BaseModel
{
    use HasFactory;

    protected $table = 'ibc_chemicals';

    protected $fillable = [
        'do_num',
        'batch_no',
        'date',
        'che_code',
        'qty',
        'expiry_date',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

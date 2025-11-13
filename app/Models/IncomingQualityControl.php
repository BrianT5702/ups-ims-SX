<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomingQualityControl extends BaseModel
{
    use HasFactory;

    protected $table = 'incoming_quality_controls';

    protected $fillable = [
        'do_num',
        'che_code',
        'date_arrived',
        'qty',
        'expiry_date',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

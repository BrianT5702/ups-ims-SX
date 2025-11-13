<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoadingUnloading extends BaseModel
{
    use HasFactory;

    protected $table = 'loading_unloadings';

    protected $fillable = [
        'tank_id',
        'che_code',
        'date',
        'start_time',
        'stop_time',
        'che_before',
        'che_after',
        'isFollowDO',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

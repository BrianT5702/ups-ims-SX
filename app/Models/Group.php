<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Group extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $table = 'groups';

    protected $fillable = ['group_name'];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('transaction_log:groups:' . config('database.default'));
        });

        static::deleted(function () {
            Cache::forget('transaction_log:groups:' . config('database.default'));
        });
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'group_id');
    }
}

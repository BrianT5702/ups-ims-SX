<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'id'; 

    protected $table = 'locations'; 

    protected $fillable = [
        'location_name',
        'position_x',
        'position_y',
        'warehouse_id'
    ];

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(){
        return $this->hasMany(Item::class);
    }
}

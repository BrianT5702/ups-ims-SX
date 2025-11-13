<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'id'; 

    protected $table = 'warehouses'; 

    protected $fillable = ['warehouse_name']; 

    public function locations() {
        return $this->hasMany(Location::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'warehouse_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Family extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'id'; 

    protected $table = 'families'; 

    protected $fillable = ['family_name']; 

    public function items()
    {
        return $this->hasMany(Item::class, 'family_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'id'; 

    protected $table = 'groups'; 

    protected $fillable = ['group_name']; 

    public function items()
    {
        return $this->hasMany(Item::class, 'group_id');
    }
}

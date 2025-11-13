<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'id'; 

    protected $table = 'categories'; 

    protected $fillable = ['cat_name']; 

    public function brands() {
        return $this->hasMany(Brand::class);
    }
    
    public function items()
    {
        return $this->hasMany(Item::class, 'cat_id');
    }
}

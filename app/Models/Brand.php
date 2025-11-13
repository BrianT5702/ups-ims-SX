<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'id'; 

    protected $table = 'brands'; 

    protected $fillable = ['brand_name']; 

    public function category() {
        return $this->belongsTo(Category::class);
    }
    
    public function items()
    {
        return $this->hasMany(Item::class, 'brand_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'warehouse_id',
        'scientific_name',
        'commercial_name',
        'company',
        'description',
        'quantity',
        'price',
        'expiration_date'
    ];
    public function favourite(){
        return $this->hasMany(Favourite::class);
    }
    public function orderMedicine(){
        return $this->hasMany(OrderMedicine::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }
}

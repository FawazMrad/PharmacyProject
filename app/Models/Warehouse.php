<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable=['warehouse_owner_id'];
    use HasFactory;

    public function warehouseOwner(){
        return $this->belongsTo(WarehouseOwner::class);
    }
    public function order(){
        return $this->hasMany(Order::class);
    }
    public function medicine(){
        return $this->hasMany(Medicine::class);
    }
}

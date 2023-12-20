<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'warehouse_id',
        'pharmacist_id',
        'date_ordered',
        'total_price',
        'status',
        'payment_status'
        ];

    public function orderMedicine(){
        return $this->belongsTo(OrderMedicine::class);
    }
    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }
    public function pharmacist(){
        return $this->belongsTo(Pharmacist::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacist extends Model
{
    protected $fillable=[
        'user_id'
    ];
    use HasFactory;

    public function user(){

        return $this->belongsTo(User::class);
    }
    public function order(){

        return $this->hasMany(Order::class);
    }
    public function favourite(){

        return $this->hasMany(Favourite::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'menu_item_id',
        'quantity',
        'name',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

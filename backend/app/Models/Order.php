<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'restaurant',
        'deliveryDetails',
        'cartItems',
        'status',
    ];

    protected $casts = [
        'user' => 'string',
        'restaurant' => 'string',
        'createdAt' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'restaurantName',
        'city',
        'country',
        'deliveryPrice',
        'estimatedDeliveryTime',
        'cuisines',
        'menuItems',
        'imageUrl',
        'lastUpdated',
    ];

    protected $casts = [
        'user' => 'string',
        'lastUpdated' => 'datetime',
    ];
}

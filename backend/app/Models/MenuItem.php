<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant',
        'name',
        'price',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        '_id',
        'auth0Id',
        'email',
        'name',
        'addressLine1',
        'city',
        'country',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

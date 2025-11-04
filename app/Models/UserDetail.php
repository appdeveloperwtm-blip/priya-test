<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image',
        'phone',
        'country',
        'country_code',
        'state',
        'city',
        'pin',
        'address1',
        'address2',
    ];

    // Relationship: One UserDetail belongs to one User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

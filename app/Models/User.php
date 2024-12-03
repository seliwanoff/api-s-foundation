<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    // Specify which attributes are mass assignable
    protected $fillable = [
        'surname',
        'firstname',
        'othername',
        'sex',
        'marital_status',
        'phoneNumber',
        'localgovernment',
        'address',
        'occupation',
        'shop_address',
        'purpose',
        'amount',
        'bvn',
        'nin',
        'level_of_education',
        'is_disabled',
        'comment',
        'bank_account_number',
        'account_name',
        'bank_name',
        'image',  // This will handle the image file path
    ];

    // Optionally, if you want to cast some attributes to specific data types
    protected $casts = [
        'is_disabled' => 'boolean',
        'amount' => 'decimal:2',
    ];

    // Optionally, add accessors or mutators for the image if needed
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
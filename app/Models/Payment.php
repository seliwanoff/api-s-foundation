<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
      'user_id',
      'amount_due',
      'amount_paid',
      'amount_remaining',
      'payment_status',
  ];


    public function getAmountRemainingAttribute()
    {
        return $this->amount_due - $this->amount_paid;
    }
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

}
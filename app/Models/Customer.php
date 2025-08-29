<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ACCOUNTNUM',
        'NAME',
        'ADDRESS',
        'PHONE',
        'CURRENCY',
        'BLOCKED',
        'CREDITMAX',
        'COUNTRY',
        'ZIPCODE',
        'STATE',
        'EMAIL',
        'CELLULARPHONE',
        'GENDER',
    ];

    public function loyaltyCard()
    {
        return $this->hasOne(LoyaltyCard::class);
    }

    public function canHaveLoyaltyCard()
    {
        return !$this->BLOCKED && !$this->loyaltyCard()->exists();
    }
}
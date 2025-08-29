<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nubersequencevalues extends Model
{
    use HasFactory;

    protected $table = 'nubersequencevalues';
    protected $primaryKey = 'STOREID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'NUMBERSEQUENCE',
        'NEXTREC',
        'CARTNEXTREC',
        'BUNDLENEXTREC',
        'DISCOUNTNEXTREC',
        'STOREID',
        'TONEXTREC',
        'STOCKNEXTREC',
    ];
}

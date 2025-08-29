<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportProducts extends Model
{
    protected $table = 'importproducts';
    
    protected $fillable = [
        'itemid',
        'description',
        'costprice',
        'salesprice',
        'searchalias',
        'notes',
        'retailgroup',
        'retaildepartment',
        'barcode',
        'activestatus',
        'barcodesetup',
    ];

    protected $casts = [
        'costprice' => 'decimal:2',
        'salesprice' => 'decimal:2',
        'activestatus' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('activestatus', 1);
    }

    public function scopeByRetailGroup($query, $group)
    {
        return $query->where('retailgroup', $group);
    }

    public function scopeWithBarcode($query)
    {
        return $query->whereNotNull('barcode')->where('barcode', '!=', '');
    }
}
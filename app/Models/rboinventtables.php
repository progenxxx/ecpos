<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rboinventtables extends Model
{
    use HasFactory;

    protected $table = 'rboinventtables';
    protected $primaryKey = 'itemid';
    public $timestamps = true;

    protected $fillable = [
        'itemid',
        'itemtype',
        'itemgroup',
        'itemdepartment',
        'zeropricevalid',
        'dateblocked',
        'datetobeblocked',
        'blockedonpos',
        'barcode',
        'datetoactivateitem',
        'mustselectuom',
        'production',
        'moq',
        'stocks',
        'transparentstocks',
        'activeondelivery',
        'default1',
        'default2',
        'default3'
    ];

    protected $casts = [
        'itemtype' => 'integer',
        'zeropricevalid' => 'boolean',
        'dateblocked' => 'datetime',
        'datetobeblocked' => 'datetime',
        'blockedonpos' => 'boolean',
        'datetoactivateitem' => 'datetime',
        'mustselectuom' => 'boolean',
        'moq' => 'integer',
        'stocks' => 'integer',
        'transparentstocks' => 'integer',
        'activeondelivery' => 'boolean',
        'default1' => 'boolean',
        'default2' => 'boolean',
        'default3' => 'boolean'
    ];

    protected $attributes = [
        'itemtype' => 0,
        'zeropricevalid' => false,
        'blockedonpos' => false,
        'mustselectuom' => false,
        'moq' => null,
        'stocks' => 0,
        'transparentstocks' => 0,
        'activeondelivery' => false,
        'default1' => false,
        'default2' => false,
        'default3' => false
    ];

    public function inventTable()
    {
        return $this->belongsTo(inventtables::class, 'itemid', 'itemid');
    }

    public function scopeActive($query)
    {
        return $query->where('activeondelivery', true);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('itemdepartment', $department);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('itemgroup', $group);
    }

    public function hasMOQ()
    {
        return !is_null($this->moq) && $this->moq > 0;
    }

    public function getFormattedMOQAttribute()
    {
        return $this->moq ? number_format($this->moq) : 'Not set';
    }
}
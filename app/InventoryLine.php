<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryLine extends Model
{
    protected $fillable = [
        'inventory_session_id',
        'product_id',
        'variant_id',
        'theoretical_qty',
        'counted_qty',
        'difference',
        'reason',
    ];

    public function session()
    {
        return $this->belongsTo(InventorySession::class, 'inventory_session_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventorySession extends Model
{
    protected $fillable = [
        'numero',
        'date_inventaire',
        'status',
        'notes',
        'user_id',
    ];

    public function lines()
    {
        return $this->hasMany(InventoryLine::class, 'inventory_session_id');
    }
}
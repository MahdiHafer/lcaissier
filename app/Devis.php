<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Devis extends Model
{
    protected $table = 'devis';

    protected $fillable = [
        'numero',
        'date_devis',
        'client_id',
        'notes',
        'total',
        'status',
        'user_id',
    ];

    public function details()
    {
        return $this->hasMany(DevisDetail::class, 'devis_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
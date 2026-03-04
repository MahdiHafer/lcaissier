<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Avoir extends Model
{
    protected $fillable = [
        'numero',
        'date_avoir',
        'client_id',
        'vente_id',
        'total',
        'notes',
        'user_id',
    ];

    public function details()
    {
        return $this->hasMany(AvoirDetail::class, 'avoir_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function vente()
    {
        return $this->belongsTo(Vente::class, 'vente_id');
    }
}
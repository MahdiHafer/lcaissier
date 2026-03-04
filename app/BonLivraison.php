<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BonLivraison extends Model
{
    protected $table = 'bons_livraison';

    protected $fillable = [
        'numero',
        'date_bon',
        'client_id',
        'notes',
        'total',
        'status',
        'user_id',
    ];

    public function details()
    {
        return $this->hasMany(BonLivraisonDetail::class, 'bon_livraison_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = [
        'numero',
        'date_facture',
        'client_id',
        'bon_livraison_id',
        'vente_id',
        'total_ht',
        'tva_rate',
        'tva_amount',
        'total_ttc',
        'legal_company_name',
        'legal_ice',
        'legal_rc',
        'legal_if',
        'legal_cnss',
        'legal_address',
        'legal_phone',
        'legal_email',
        'notes',
        'user_id',
    ];

    public function details()
    {
        return $this->hasMany(FactureDetail::class, 'facture_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}


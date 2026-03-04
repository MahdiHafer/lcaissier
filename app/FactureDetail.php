<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactureDetail extends Model
{
    protected $fillable = [
        'facture_id',
        'product_id',
        'designation',
        'quantite',
        'prix_unitaire',
        'total_ligne',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }
}


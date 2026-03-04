<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AvoirDetail extends Model
{
    protected $table = 'avoir_details';

    protected $fillable = [
        'avoir_id',
        'vente_detail_id',
        'product_id',
        'variant_id',
        'reference_produit',
        'nom_produit',
        'quantite',
        'prix_unitaire',
        'total_ligne',
    ];

    public function avoir()
    {
        return $this->belongsTo(Avoir::class, 'avoir_id');
    }
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BonLivraisonDetail extends Model
{
    protected $table = 'bon_livraison_details';

    protected $fillable = [
        'bon_livraison_id',
        'vente_id',
        'product_id',
        'image',
        'designation',
        'quantite',
        'quantite_vendue',
        'prix_unitaire',
        'total_ligne',
    ];

    public function bonLivraison()
    {
        return $this->belongsTo(BonLivraison::class, 'bon_livraison_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function vente()
    {
        return $this->belongsTo(Vente::class, 'vente_id');
    }
}

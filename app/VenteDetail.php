<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VenteDetail extends Model
{
    protected $fillable = [
        'vente_id', 'reference_produit', 'variant_id', 'nom_produit',
        'quantite', 'quantite_retournee', 'prix_unitaire', 'total_ligne'
    ];
 protected $table = 'ventes_details'; 
    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

public function produit()
{
    return $this->belongsTo(Product::class, 'reference_produit', 'id');
}

public function variant()
{
    return $this->belongsTo(ProductVariant::class, 'variant_id');
}

public function avoirDetails()
{
    return $this->hasMany(AvoirDetail::class, 'vente_detail_id');
}


}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'reference',
        'categorie',
        'etat',
        'tva_rate',
        'marque',
        'modele',
        'ram',
        'stockage',
        'processeur',
        'prix_achat_ht',
        'prix_achat_ttc',
        'prix_vente_ht',
        'prix_vente_ttc',
        'prix_achat',
        'prix_vente',
        'quantite',
        'has_variants',
        'codebar',
        'fournisseur_id',
        'image',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}

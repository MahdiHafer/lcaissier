<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DevisDetail extends Model
{
    protected $table = 'devis_details';

    protected $fillable = [
        'devis_id',
        'product_id',
        'image',
        'designation',
        'quantite',
        'prix_unitaire',
        'total_ligne',
    ];

    public function devis()
    {
        return $this->belongsTo(Devis::class, 'devis_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['nom', 'societe', 'ice', 'rc', 'if_fiscal', 'telephone', 'email', 'adresse'];

    public function ventes()
    {
        return $this->hasMany(Vente::class, 'client');
    }

    public function bonsLivraison()
    {
        return $this->hasMany(BonLivraison::class, 'client_id');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class, 'client_id');
    }

    public function devis()
    {
        return $this->hasMany(Devis::class, 'client_id');
    }

    public function avoirs()
    {
        return $this->hasMany(Avoir::class, 'client_id');
    }
}

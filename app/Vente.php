<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    protected $fillable = [
        'numero_ticket', 'total', 'remise', 'net_a_payer', 'montant_paye',
        'rendu', 'mode_paiement', 'client', 'user_id'
    ];

    public function details()
    {
        return $this->hasMany(VenteDetail::class);
    }

    public function clientInfo()
{
    return $this->belongsTo(Client::class, 'client'); // le champ s'appelle `client`
}

    public function avoirs()
    {
        return $this->hasMany(Avoir::class, 'vente_id');
    }

}

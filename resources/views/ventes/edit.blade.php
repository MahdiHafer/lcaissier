@extends('layouts.app')

@section('content')

<style>
:root{
    --primary:#007aff;
    --primary-light:#e6f1ff;
    --border:#e5e7eb;
    --text-dark:#111827;
    --text-muted:#6b7280;
    --bg-page:#f5f7fb;
    --card-bg:#ffffff;
}

body{
    background:var(--bg-page);
}

/* title */
.title-pos{
    color:var(--text-dark);
    font-weight:700;
}

/* card */
.card-pos{
    background:var(--card-bg);
    border-radius:16px;
    border:1px solid var(--border);
    box-shadow:0 8px 25px rgba(0,0,0,.05);
}

/* labels */
.label-pos{
    font-weight:600;
    color:var(--text-dark);
}

/* inputs */
.input-pos{
    background:#fff;
    border:1px solid var(--border);
    border-radius:999px;
    padding:10px 16px;
}

.input-pos:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 3px var(--primary-light);
}

/* info cards */
.info-card{
    background:#f8fafc;
    border-radius:12px;
    padding:15px;
    border:1px solid var(--border);
    text-align:center;
}

.net{
    color:#059669;
    font-weight:700;
    font-size:18px;
}

.reste{
    color:#d97706;
    font-weight:700;
    font-size:18px;
}

/* button */
.btn-primary-pos{
    background:var(--primary);
    border:none;
    border-radius:999px;
    padding:10px 25px;
    color:white;
    font-weight:600;
}

.btn-primary-pos:hover{
    background:#0066d6;
}
</style>



<div class="container">

<h2 class="title-pos mb-4">
Modifier Vente #{{ $vente->numero_ticket }}
</h2>


<div class="card-pos p-4">

<form action="{{ route('ventes.update.simple',$vente->id) }}"
method="POST">

@csrf


<div class="row mb-4">


<div class="col-md-4">

<label class="label-pos">
Mode de paiement
</label>

@php
$mode = strtolower(trim($vente->mode_paiement));

$modeMap = [
'espèces'=>'especes',
'especes'=>'especes',
'tpe'=>'tpe',
'virement'=>'virement',
'crédit'=>'credit',
'credit'=>'credit',
'chèque'=>'cheque',
'cheque'=>'cheque',
];

$modeNormalise = $modeMap[$mode] ?? 'especes';
@endphp

<select name="mode_paiement"
id="mode_paiement"
class="form-control input-pos">

<option value="especes" {{ $modeNormalise==='especes'?'selected':'' }}>
Espèces
</option>

<option value="tpe" {{ $modeNormalise==='tpe'?'selected':'' }}>
TPE
</option>

<option value="virement" {{ $modeNormalise==='virement'?'selected':'' }}>
Virement
</option>

<option value="credit" {{ $modeNormalise==='credit'?'selected':'' }}>
Crédit
</option>

<option value="cheque" {{ $modeNormalise==='cheque'?'selected':'' }}>
Chèque
</option>

</select>

</div>



<div class="col-md-4">

<label class="label-pos">
Remise
</label>

<input type="number"
step="0.01"
name="remise"
class="form-control input-pos"
value="{{ $vente->remise }}">

</div>



<div class="col-md-4">

<label class="label-pos">
Montant payé
</label>

<input type="number"
step="0.01"
name="montant_paye"
class="form-control input-pos"
value="{{ $vente->montant_paye }}">

</div>


</div>



<!-- INFO -->
<div class="row mb-4">

<div class="col-md-6">

<div class="info-card">

Net à payer

<div class="net"
id="net_affiche">

{{ number_format($vente->net_a_payer,2) }}

</div>

DH

</div>

</div>



<div class="col-md-6">

<div class="info-card">

Reste à payer

<div class="reste"
id="reste_affiche">

{{ number_format($vente->net_a_payer - $vente->montant_paye,2) }}

</div>

DH

</div>

</div>

</div>



<div class="text-end">

<button type="submit"
class="btn btn-primary-pos">

Enregistrer les modifications

</button>

</div>



</form>

</div>

</div>



<script>

const total = {{ $vente->total }};

const remiseInput = document.querySelector('input[name="remise"]');

const payeInput = document.querySelector('input[name="montant_paye"]');

const netAffiche = document.getElementById('net_affiche');

const resteAffiche = document.getElementById('reste_affiche');


function recalculer(){

const remise = parseFloat(remiseInput.value)||0;

const paye = parseFloat(payeInput.value)||0;

const net = Math.max(total - remise,0);

const reste = Math.max(net - paye,0);

netAffiche.textContent = net.toFixed(2);

resteAffiche.textContent = reste.toFixed(2);

}


remiseInput.addEventListener('input',recalculer);

payeInput.addEventListener('input',recalculer);

</script>


@endsection

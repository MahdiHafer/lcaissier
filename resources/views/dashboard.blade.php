@extends('layouts.app')

@section('content')
<style>
:root {
    --dash-bg: #f3f7f5;
    --dash-card: #ffffff;
    --dash-line: #dce8e2;
    --dash-text: #102018;
    --dash-muted: #61786c;
    --dash-strong: #0f9d64;
    --dash-strong-2: #0c7a4e;
    --dash-accent: #14b8a6;
    --dash-warn: #efb100;
    --dash-danger: #dc2626;
}

body {
    background:
        radial-gradient(900px 320px at 8% -10%, #e4f8ef 0%, transparent 68%),
        radial-gradient(900px 320px at 100% 0%, #e7f4ff 0%, transparent 60%),
        var(--dash-bg);
}

.dashboard-shell {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.glass {
    background: rgba(255, 255, 255, .82);
    border: 1px solid var(--dash-line);
    border-radius: 18px;
    box-shadow: 0 20px 46px rgba(17, 47, 33, .09);
    backdrop-filter: blur(8px);
}

.headbar {
    padding: 14px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px;
    align-items: center;
}

.head-title {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: var(--dash-text);
    letter-spacing: -.3px;
}

.head-sub {
    color: var(--dash-muted);
    font-size: 13px;
}

.filters {
    padding: 14px;
}

.filters .form-label {
    color: #2d4439;
    font-size: 12px;
    font-weight: 700;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.kpi {
    padding: 14px;
    border-radius: 16px;
    border: 1px solid var(--dash-line);
    background: linear-gradient(180deg, #fff 0%, #f9fcfa 100%);
}

.kpi-label {
    font-size: 12px;
    color: var(--dash-muted);
    font-weight: 700;
}

.kpi-value {
    margin-top: 4px;
    font-size: 26px;
    line-height: 1;
    font-weight: 800;
    color: var(--dash-text);
}

.kpi-note {
    margin-top: 6px;
    font-size: 11px;
    color: #6f8579;
}

.kpi.emphasis .kpi-value { color: var(--dash-strong-2); }
.kpi.warn .kpi-value { color: #9a7400; }
.kpi.danger .kpi-value { color: var(--dash-danger); }

.grid-2 {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 12px;
}

.grid-2b {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.panel {
    padding: 14px;
    border: 1px solid var(--dash-line);
    border-radius: 16px;
    background: var(--dash-card);
}

.panel-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 8px;
}

.panel-title {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: var(--dash-text);
}

.panel-sub {
    font-size: 12px;
    color: var(--dash-muted);
}

.chart-wrap {
    position: relative;
    height: 290px;
}

.chart-wrap.small {
    height: 240px;
}

.table-wrap {
    max-height: 350px;
    overflow: auto;
    border: 1px solid var(--dash-line);
    border-radius: 12px;
}

.table-modern {
    margin: 0;
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.table-modern th,
.table-modern td {
    padding: 10px 11px;
    border-bottom: 1px solid #e8f0eb;
}

.table-modern th {
    position: sticky;
    top: 0;
    background: #f4faf7;
    color: #27473a;
    font-weight: 800;
    font-size: 12px;
    text-transform: uppercase;
}

.table-modern tr:hover td {
    background: #f7fcf9;
}

.badge-soft {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    background: #e7f7ef;
    color: #16784d;
    font-size: 11px;
    font-weight: 700;
}

@media (max-width: 1200px) {
    .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .grid-2, .grid-2b { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .headbar { grid-template-columns: 1fr; }
    .kpi-grid { grid-template-columns: 1fr; }
    .head-title { font-size: 24px; }
}
</style>

@php
    $margePercent = $ca_net > 0 ? (($marge / $ca_net) * 100) : 0;
    $avgDaily = $ventesParJour->count() > 0 ? ($ca_net / $ventesParJour->count()) : 0;
@endphp

<div class="container-fluid dashboard-shell">
    <section class="glass headbar">
        <div>
            <h2 class="head-title">Dashboard Commercial</h2>
            <div class="head-sub">Pilotage CA, marge, stock et performances produits en temps reel.</div>
        </div>
        <div class="text-md-end">
            <span class="badge-soft">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </section>

    <section class="glass filters">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Date debut</label>
                <input type="date" name="start" class="form-control" value="{{ $start }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date fin</label>
                <input type="date" name="end" class="form-control" value="{{ $end }}">
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-success">Filtrer le dashboard</button>
            </div>
            <div class="col-md-3 d-grid">
                <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary">Reinitialiser</a>
            </div>
        </form>
    </section>

    <section class="kpi-grid">
        <article class="kpi emphasis">
            <div class="kpi-label">Chiffre d'affaires net</div>
            <div class="kpi-value">{{ number_format($ca_net, 2) }} DH</div>
            <div class="kpi-note">Brut: {{ number_format($ca_brut, 2) }} DH | Remises: {{ number_format($total_remise, 2) }} DH</div>
        </article>
        <article class="kpi">
            <div class="kpi-label">Marge nette</div>
            <div class="kpi-value">{{ number_format($marge, 2) }} DH</div>
            <div class="kpi-note">Taux marge: {{ number_format($margePercent, 2) }}%</div>
        </article>
        <article class="kpi warn">
            <div class="kpi-label">Valeur stock</div>
            <div class="kpi-value">{{ number_format($valeur_stock, 2) }} DH</div>
            <div class="kpi-note">Articles en stock: {{ number_format($total_articles) }}</div>
        </article>
        <article class="kpi danger">
            <div class="kpi-label">Credits restants</div>
            <div class="kpi-value">{{ number_format($credits_restants, 2) }} DH</div>
            <div class="kpi-note">Ventes: {{ $ventes_count }} | Ticket moyen: {{ number_format($ticket_moyen, 2) }} DH</div>
        </article>
    </section>

    <section class="grid-2">
        <article class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Evolution du CA</h3>
                <span class="panel-sub">Moyenne/jour: {{ number_format($avgDaily, 2) }} DH</span>
            </div>
            <div class="chart-wrap">
                <canvas id="caChart"></canvas>
            </div>
        </article>
        <article class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Repartition paiements</h3>
                <span class="panel-sub">Par mode de paiement</span>
            </div>
            <div class="chart-wrap">
                <canvas id="payChart"></canvas>
            </div>
        </article>
    </section>

    <section class="grid-2b">
        <article class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Top produits</h3>
                <span class="panel-sub">Top 8 par quantite</span>
            </div>
            <div class="chart-wrap small">
                <canvas id="topProductsChart"></canvas>
            </div>
        </article>
        <article class="panel">
            <div class="panel-head">
                <h3 class="panel-title">Valeur stock par categorie</h3>
                <span class="panel-sub">Valorisation actuelle</span>
            </div>
            <div class="chart-wrap small">
                <canvas id="stockByCatChart"></canvas>
            </div>
        </article>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h3 class="panel-title">Top 20 produits vendus</h3>
            <span class="panel-sub">Classement detaille</span>
        </div>
        <div class="table-wrap">
            <table class="table-modern">
                <thead>
                <tr>
                    <th>Designation</th>
                    <th>Quantite vendue</th>
                    <th>CA genere</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($topProduits as $produit)
                    <tr>
                        <td>{{ $produit['designation'] }}</td>
                        <td>{{ number_format($produit['quantite']) }}</td>
                        <td>{{ number_format($produit['ca'], 2) }} DH</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Aucune vente sur cette periode.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
const caLabels = @json($ventesParJour->keys()->map(function($d){ return \Carbon\Carbon::parse($d)->format('d/m'); })->values());
const caValues = @json($ventesParJour->values());
const payLabels = @json($paiementsBreakdown->keys()->values());
const payValues = @json($paiementsBreakdown->values());
const topLabels = @json($topProduitsChart->pluck('designation')->map(function($v){ return \Illuminate\Support\Str::limit($v, 24); })->values());
const topValues = @json($topProduitsChart->pluck('quantite')->values());
const stockCatLabels = @json($valeur_stock_par_categorie->pluck('categorie')->map(function($v){ return $v ?: 'Sans categorie'; })->values());
const stockCatValues = @json($valeur_stock_par_categorie->pluck('total')->values());

const palette = {
    green: '#0f9d64',
    greenSoft: 'rgba(15,157,100,.18)',
    teal: '#14b8a6',
    blue: '#0ea5e9',
    amber: '#eab308',
    red: '#ef4444',
    line: '#d5e4dc'
};

function baseOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: '#27473a', boxWidth: 12, usePointStyle: true }
            },
            tooltip: {
                backgroundColor: '#102018',
                titleColor: '#fff',
                bodyColor: '#e8f4ed',
                borderColor: '#1f5a40',
                borderWidth: 1
            }
        },
        scales: {
            x: {
                ticks: { color: '#466559' },
                grid: { color: 'rgba(213,228,220,.45)' }
            },
            y: {
                ticks: { color: '#466559' },
                grid: { color: 'rgba(213,228,220,.45)' },
                beginAtZero: true
            }
        }
    };
}

if (document.getElementById('caChart')) {
    new Chart(document.getElementById('caChart'), {
        type: 'line',
        data: {
            labels: caLabels,
            datasets: [{
                label: 'CA net (DH)',
                data: caValues,
                borderColor: palette.green,
                backgroundColor: palette.greenSoft,
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 4,
                tension: .35,
                fill: true
            }]
        },
        options: baseOptions()
    });
}

if (document.getElementById('payChart')) {
    new Chart(document.getElementById('payChart'), {
        type: 'doughnut',
        data: {
            labels: payLabels,
            datasets: [{
                data: payValues,
                backgroundColor: [palette.green, palette.teal, palette.blue, palette.amber, '#8b5cf6', palette.red, '#64748b'],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#27473a', boxWidth: 12, usePointStyle: true }
                },
                tooltip: {
                    backgroundColor: '#102018',
                    titleColor: '#fff',
                    bodyColor: '#e8f4ed'
                }
            }
        }
    });
}

if (document.getElementById('topProductsChart')) {
    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: topLabels,
            datasets: [{
                label: 'Quantite',
                data: topValues,
                backgroundColor: 'rgba(20,184,166,.7)',
                borderColor: '#0f766e',
                borderWidth: 1.4,
                borderRadius: 8,
                maxBarThickness: 26
            }]
        },
        options: {
            ...baseOptions(),
            indexAxis: 'y'
        }
    });
}

if (document.getElementById('stockByCatChart')) {
    new Chart(document.getElementById('stockByCatChart'), {
        type: 'bar',
        data: {
            labels: stockCatLabels,
            datasets: [{
                label: 'Valeur stock (DH)',
                data: stockCatValues,
                backgroundColor: 'rgba(234,179,8,.65)',
                borderColor: '#b88b00',
                borderWidth: 1.2,
                borderRadius: 8,
                maxBarThickness: 34
            }]
        },
        options: baseOptions()
    });
}
</script>
@endsection

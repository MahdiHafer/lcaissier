@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Inventaire {{ $inventory->numero }}</h2>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card p-3 mb-3">
        <div class="row">
            <div class="col-md-3"><strong>Date:</strong> {{ \Carbon\Carbon::parse($inventory->date_inventaire)->format('d/m/Y') }}</div>
            <div class="col-md-3"><strong>Statut:</strong> <span class="badge {{ $inventory->status === 'valide' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($inventory->status) }}</span></div>
            <div class="col-md-6"><strong>Notes:</strong> {{ $inventory->notes ?: '-' }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('inventory.save', $inventory) }}">
        @csrf
        <div class="table-responsive card p-2">
            <table class="table table-hover align-middle mb-0" id="invTable">
                <thead>
                <tr>
                    <th>Produit</th>
                    <th>Variante</th>
                    <th>Theorique</th>
                    <th>Comptee</th>
                    <th>Ecart</th>
                    <th>Motif ecart</th>
                </tr>
                </thead>
                <tbody>
                @foreach($inventory->lines as $line)
                    <tr>
                        <td>{{ optional($line->product)->marque ?: '-' }}</td>
                        <td>
                            @if($line->variant)
                                {{ $line->variant->size ?: '-' }} / {{ optional($line->variant->color)->name ?: '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="theo">{{ $line->theoretical_qty }}</td>
                        <td style="max-width:110px;">
                            <input type="number" min="0" class="form-control counted" name="counted_qty[{{ $line->id }}]" value="{{ $line->counted_qty }}" {{ $inventory->status === 'valide' ? 'readonly' : '' }}>
                        </td>
                        <td class="diff {{ $line->difference != 0 ? 'fw-bold text-danger' : '' }}">{{ $line->difference }}</td>
                        <td>
                            <input type="text" class="form-control" name="reason[{{ $line->id }}]" value="{{ $line->reason }}" placeholder="Casse, perte, erreur reception..." {{ $inventory->status === 'valide' ? 'readonly' : '' }}>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($inventory->status !== 'valide')
            <div class="text-end mt-3 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-outline-primary">Enregistrer le comptage</button>
                <button type="submit" formaction="{{ route('inventory.validate', $inventory) }}" class="btn btn-success" onclick="return confirm('Valider cet inventaire et appliquer les ecarts au stock ?')">Valider inventaire</button>
            </div>
        @endif
    </form>
</div>
@endsection

@section('scripts')
<script>
const rows = Array.from(document.querySelectorAll('#invTable tbody tr'));
rows.forEach((row) => {
    const theo = Number(row.querySelector('.theo')?.textContent || 0);
    const counted = row.querySelector('.counted');
    const diffEl = row.querySelector('.diff');
    if (!counted || !diffEl) return;

    const recalc = () => {
        const diff = Number(counted.value || 0) - theo;
        diffEl.textContent = diff;
        diffEl.classList.toggle('fw-bold', diff !== 0);
        diffEl.classList.toggle('text-danger', diff !== 0);
    };

    counted.addEventListener('input', recalc);
    recalc();
});
</script>
@endsection
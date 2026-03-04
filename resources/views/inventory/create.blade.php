@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">Nouvel inventaire</h2>

    <form method="POST" action="{{ route('inventory.store') }}" class="card p-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Date inventaire</label>
                <input type="date" name="date_inventaire" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="Ex: inventaire mensuel, comptage equipe A...">
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary">Creer la session inventaire</button>
            </div>
        </div>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Modifier l'utilisateur</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update',$user) }}" method="POST" class="card p-4">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Code d'acces (4 a 12 chiffres)</label>
                <input type="text" name="access_code" class="form-control" value="{{ old('access_code', $user->access_code) }}" required inputmode="numeric" pattern="[0-9]{4,12}" maxlength="12">
            </div>

            <div class="col-md-6">
                <label class="form-label">Nouveau mot de passe (optionnel)</label>
                <input type="password" name="password" class="form-control">
                <small class="text-muted">Laisser vide si inchange</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="agent" {{ old('role', $user->role)==='agent' ? 'selected' : '' }}>Agent</option>
                    <option value="admin" {{ old('role', $user->role)==='admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Mettre a jour</button>
            </div>
        </div>
    </form>
</div>
@endsection
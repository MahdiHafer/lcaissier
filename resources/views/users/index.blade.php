@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Utilisateurs</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">+ Ajouter un utilisateur</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Code d'acces</th>
                    <th>Role</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-dark">{{ $user->access_code ?: '-' }}</span></td>
                    <td>
                        <span class="badge {{ $user->role == 'admin' ? 'bg-primary' : 'bg-secondary' }}">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('users.edit',$user) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <form action="{{ route('users.destroy',$user) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
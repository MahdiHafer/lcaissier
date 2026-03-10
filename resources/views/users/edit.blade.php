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
                <select name="role" id="roleSelect" class="form-select" required>
                    <option value="agent" {{ old('role', $user->role)==='agent' ? 'selected' : '' }}>Agent</option>
                    <option value="admin" {{ old('role', $user->role)==='admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div class="col-12">
                <div class="border rounded-3 p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label m-0">Modules, fonctionnalites et options accessibles</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="checkAllPerms">Tout cocher</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="uncheckAllPerms">Tout decocher</button>
                        </div>
                    </div>

                    @php
                        $currentPermissions = old('permissions', is_array($user->permissions) ? $user->permissions : $defaultAgentPermissions);
                    @endphp
                    @foreach($permissionSections as $section => $perms)
                        <div class="mb-2">
                            <div class="fw-semibold">{{ $section }}</div>
                            <div class="row g-2">
                                @foreach($perms as $key => $label)
                                    @php $checked = in_array($key, $currentPermissions, true); @endphp
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input perm-box" type="checkbox" name="permissions[]" id="perm_{{ md5($key) }}" value="{{ $key }}" {{ $checked ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ md5($key) }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Mettre a jour</button>
            </div>
        </div>
    </form>
</div>

<script>
const roleSelect = document.getElementById('roleSelect');
const permBoxes = Array.from(document.querySelectorAll('.perm-box'));
const checkAllPermsBtn = document.getElementById('checkAllPerms');
const uncheckAllPermsBtn = document.getElementById('uncheckAllPerms');

function applyRolePermissionsState() {
    const isAdmin = roleSelect.value === 'admin';
    permBoxes.forEach((box) => {
        if (isAdmin) {
            box.checked = true;
            box.disabled = true;
        } else {
            box.disabled = false;
        }
    });
}

roleSelect.addEventListener('change', applyRolePermissionsState);
checkAllPermsBtn.addEventListener('click', () => permBoxes.forEach((box) => { if (!box.disabled) box.checked = true; }));
uncheckAllPermsBtn.addEventListener('click', () => permBoxes.forEach((box) => { if (!box.disabled) box.checked = false; }));
applyRolePermissionsState();
</script>
@endsection

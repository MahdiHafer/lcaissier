@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-3">Nouveau devis</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('devis._form')
</div>
@endsection
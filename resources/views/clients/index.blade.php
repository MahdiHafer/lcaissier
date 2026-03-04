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
    background:#fff;
    border-radius:16px;
    border:1px solid var(--border);
    box-shadow:0 8px 25px rgba(0,0,0,.05);
}

/* input */
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

/* buttons */
.btn-primary-pos{
    background:var(--primary);
    border:1px solid var(--primary);
    color:#fff;
    border-radius:999px;
    padding:10px 20px;
    font-weight:600;
}

.btn-primary-pos:hover{
    background:#0066d6;
}

.btn-outline-primary-pos{
    border:1px solid var(--primary);
    color:var(--primary);
    background:#fff;
    border-radius:999px;
}

.btn-outline-primary-pos:hover{
    background:var(--primary);
    color:#fff;
}

.btn-outline-secondary-pos{
    border:1px solid var(--border);
    color:var(--text-muted);
    background:#fff;
    border-radius:999px;
}

.btn-outline-secondary-pos:hover{
    background:#f3f4f6;
}

/* table */
.table-pos{
    background:#fff;
}

.table-pos thead{
    background:#f9fafb;
}

.table-pos th{
    color:var(--text-muted);
    font-weight:600;
}

.table-pos td{
    color:var(--text-dark);
}

.empty-pos{
    color:var(--text-muted);
}
</style>


<div class="container-fluid">


    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="title-pos">
            Gestion des Clients
        </h2>

        <a href="{{ route('clients.create') }}"
           class="btn btn-primary-pos">

            + Ajouter un client

        </a>

    </div>



    <!-- SEARCH -->
    <form method="GET"
          action="{{ route('clients.index') }}"
          class="mb-4">

        <div class="row g-2">

            <div class="col-md-4">

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control input-pos"
                       placeholder="Rechercher par nom, téléphone ou email">

            </div>


            <div class="col-md-2">

                <button type="submit"
                        class="btn btn-outline-primary-pos w-100">

                    Rechercher

                </button>

            </div>


            <div class="col-md-1">

                <a href="{{ route('clients.index') }}"
                   class="btn btn-outline-secondary-pos w-100">

                    ❌

                </a>

            </div>

        </div>

    </form>



    <!-- TABLE -->
    <div class="table-responsive card-pos">

        <table class="table table-pos table-hover align-middle">

            <thead>

                <tr>

                    <th>Nom</th>

                    <th>Téléphone</th>

                    <th>Email</th>

                    <th>Adresse</th>

                    <th class="text-end">
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($clients as $client)

                <tr>

                    <td>
                        {{ $client->nom }}
                    </td>


                    <td>
                        {{ $client->telephone ?? '—' }}
                    </td>


                    <td>
                        {{ $client->email ?? '—' }}
                    </td>


                    <td>
                        {{ $client->adresse ?? '—' }}
                    </td>


                    <td class="text-end">
                        <a href="{{ route('clients.show',$client) }}"
                           class="btn btn-sm btn-outline-secondary-pos">
                            Dossier
                        </a>

                        <a href="{{ route('clients.edit',$client) }}"
                           class="btn btn-sm btn-outline-primary-pos">

                            Modifier

                        </a>


                        <form action="{{ route('clients.destroy',$client) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Supprimer ce client ?');">

                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-outline-primary-pos">

                                Supprimer

                            </button>

                        </form>


                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="5"
                        class="text-center empty-pos">

                        Aucun client trouvé.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>


</div>

@endsection

<!-- resources/views/home.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        @guest
            <div class="text-center py-5">
                <h1 class="display-4">Benvenuto su UniLab</h1>
                <p class="lead">Il tuo gestionale per la gestione dei progetti di ricerca.</p>
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Accedi</a>
                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">Registrati</a>
            </div>
        @endguest
        @auth
        <h1>Benvenuto sul tuo gestionale di ricerca</h1>
        <p class="lead">Il tuo punto di riferimento per la gestione dei progetti di ricerca.</p>

        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5">Progetti totali</h2>
                <div class="mt-2">
                    Attualmente ci sono <strong>{{ $projectCount }}</strong> progetti.
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h5">Il tuo gruppo di ricerca</h2>
                @if($group)
                <div class="mt-2">
                    Sei membro del gruppo di ricerca <strong>{{ $group->name }}</strong>.
                    <div class="text-muted small">{{ $group->description }}</div>
                    <div class="mt-2">
                        Membri del gruppo:
                        <ul>
                            @foreach($group->users as $u)
                                <li>{{ $u->name }} ({{ $u->email }})</li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('groups.edit') }}" class="btn btn-primary mt-2">Gestisci il tuo gruppo</a>
                </div>
                @else
                <p class="text-muted">Non sei ancora associato a nessun gruppo di ricerca.</p>
                @endif
            </div>
        </div>
        @endauth
    </div>
@endsection

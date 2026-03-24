@extends('layouts.app')

@section('content')
    <a href="{{ route('home') }}" class="btn btn-link p-0 mb-3">← Torna alla Home</a>
    <div class="container">
        <h1>Modifica Gruppo di Ricerca</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('groups.update') }}" method="POST" class="mb-4">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nome del Gruppo</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $group->name }}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descrizione</label>
                <textarea name="description" id="description" class="form-control">{{ $group->description }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Salva Modifiche</button>
        </form>
    </div>
@endsection
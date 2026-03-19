@extends('layouts.app')

@section('content')
    @php
        $statusMap = [
            'draft'     => 'Pianificato',
            'planned'   => 'Pianificato',
            'ongoing'   => 'In Corso',
            'active'    => 'Completato',
            'completed' => 'Completato',
        ];
    @endphp

    <h2 class="h5 mb-3">Progetti di Ricerca</h2>

    <a href="{{ route('projects.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm mb-4">
        + Nuovo progetto
    </a>

    @forelse ($projects as $p)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div><h3 class="h6 m-0">{{ $p->title }}</h3></div>
                    <div class="text-muted small">
                        <strong> Status:</strong> {{ $p->status ? ($statusMap[$p->status] ?? ucfirst($p->status)) : 'n/d' }}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-primary"
                       href="{{ route('projects.show', $p) }}">
                        Dettagli
                    </a>

                    <a class="btn btn-sm btn-outline-warning"
                       href="{{ route('projects.edit', $p) }}">
                        Modifica
                    </a>

                    <form action="{{ route('projects.destroy', $p) }}"
                          method="POST"
                          onsubmit="return confirm('Sei sicuro di eliminare questo progetto?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">
                            Elimina
                        </button>
                    </form>
                </div>


                @if($p->tags->isNotEmpty())
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        @foreach($p->tags as $t)
                            <span class="badge text-bg-info">#{{ $t->name }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-2 text-muted small">
                    Milestone: {{ $p->milestones->count() }} |
                    Pubblicazioni collegate: {{ $p->publications->count() }}
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-secondary">Nessun progetto presente.</div>
    @endforelse
@endsection

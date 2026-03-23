@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('publications.index') }}" class="btn btn-link p-0 mb-3 text-decoration-none">
            ← Torna a Pubblicazioni
        </a>

        @if(session('success'))
            <div class="alert alert-success mb-4 rounded">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            {{-- COLONNA SINISTRA (Contenuto Principale) --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="h3 fw-bold mb-0">
                                {{ $publication->title }}
                            </h2>

                            <div class="d-flex gap-2 flex-shrink-0 ms-3">
                                <a href="{{ route('publications.export', $publication) }}" class="btn btn-success btn-sm fw-bold shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Esporta CSV
                                </a>
                                @if(Auth::check() && in_array(Auth::user()->role, ['pi','manager']))
                                    <a href="{{ route('publications.edit', $publication) }}" class="btn btn-sm btn-outline-warning fw-bold px-3">
                                        Modifica
                                    </a>
                                @endif
                                <form action="{{ route('publications.destroy', $publication) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa pubblicazione?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3">
                                        Elimina
                                    </button>
                                </form>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h5 class="fw-bold mb-3">File Allegati</h5>
                        @if($publication->attachments->count() > 0)
                            <ul class="list-group list-group-flush mb-4">
                                @foreach($publication->attachments as $att)
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 mb-2">
                                        <div class="text-secondary" style="word-break: break-all;">
                                            {{ basename($att->path) }}
                                        </div>
                                        <a class="btn btn-sm btn-outline-primary fw-bold px-3 ms-3 flex-shrink-0" href="{{ \Illuminate\Support\Facades\Storage::url($att->path) }}" target="_blank">
                                            Scarica
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-muted fst-italic">
                                Nessun file caricato per questa pubblicazione.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- COLONNA DESTRA (Dettagli Pubblicazione) --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light fw-bold py-3">
                        Dettagli Pubblicazione
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">

                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fw-bold small">Stato</span>
                                @php
                                    $statusClass = 'bg-secondary';
                                    $statusLabel = ucfirst($publication->status ?? 'N/D');

                                    if ($publication->status === 'published') {
                                        $statusClass = 'bg-success';
                                        $statusLabel = 'Pubblicata';
                                    } elseif ($publication->status === 'accepted') {
                                        $statusClass = 'bg-warning text-dark';
                                        $statusLabel = 'Accettata';
                                    } elseif ($publication->status === 'submitted') {
                                        $statusClass = 'bg-primary';
                                        $statusLabel = 'Inviata';
                                    } elseif ($publication->status === 'drafting') {
                                        $statusClass = 'bg-danger';
                                        $statusLabel = 'In Bozza';
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">
                                    {{ $statusLabel }}
                                </span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fw-bold small">Tipo</span>
                                <span class="fw-bold">{{ ucfirst($publication->type ?? 'N/D') }}</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fw-bold small">Venue</span>
                                <span class="fw-bold">{{ $publication->venue ?? 'N/D' }}</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fw-bold small">DOI</span>
                                <span class="fw-bold">{{ $publication->doi ?? 'N/D' }}</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fw-bold small">Target deadline</span>
                                <span class="fw-bold">
                                    @if($publication->target_deadline)
                                        {{ \Carbon\Carbon::parse($publication->target_deadline)->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted fst-italic">Nessuna</span>
                                    @endif
                                </span>
                            </li>

                            <li class="list-group-item py-3">
                                <div class="text-muted fw-bold small mb-2">Autori</div>
                                <div>
                                    @if($publication->authors->count() > 0)
                                        <ol class="mb-0 ps-3">
                                            @foreach($publication->authors->sortBy('order') as $a)
                                                <li class="mb-1">
                                                    <span class="fw-bold text-primary">{{ $a->user->name ?? 'N/D' }}</span>
                                                    @if(optional($a)->is_corresponding)
                                                        <sup class="text-danger fw-bold">*</sup>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <span class="text-muted fst-italic">-- Nessun autore --</span>
                                    @endif
                                </div>
                            </li>

                            <li class="list-group-item py-3">
                                <div class="text-muted fw-bold small mb-2">Progetti collegati</div>
                                <div>
                                    @forelse($publication->projects as $pr)
                                        <div class="mb-1">
                                            <a href="{{ route('projects.show', $pr->id) }}" class="text-decoration-none fw-bold">
                                                {{ $pr->title }}
                                            </a>
                                        </div>
                                    @empty
                                        <span class="text-muted fst-italic">-- Nessun progetto --</span>
                                    @endforelse
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

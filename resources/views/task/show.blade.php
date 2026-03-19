@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('tasks.index') }}" class="btn btn-link p-0 mb-3 text-decoration-none">
            ← Torna a Tasks
        </a>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="h3 fw-bold mb-0">
                                {{ str_replace('Task: ', '', $task->title) }}
                            </h2>

                            <div class="d-flex gap-2">
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-warning fw-bold px-3">
                                    Modifica
                                </a>
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa task?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3">
                                        Elimina
                                    </button>
                                </form>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h5 class="fw-bold mb-3">Descrizione</h5>
                        @if($task->description)
                            <div class="text-secondary" style="white-space: pre-line;">
                                {{ $task->description }}
                            </div>
                        @else
                            <div class="text-muted fst-italic">
                                Nessuna descrizione inserita per questa task.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold py-3">Commenti</div>
                    <div class="card-body p-4">
                        <form action="{{ route('tasks.comments.store', $task->id) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="body" class="form-control" placeholder="Scrivi un commento..." required>
                                <button class="btn btn-primary fw-bold px-4" type="submit">Invia</button>
                            </div>
                        </form>

                        <div class="vstack gap-3" style="max-height: 250px; overflow-y: auto;">
                            @forelse($task->comments as $c)
                                <div class="bg-light p-3 rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small text-primary">{{ optional($c->user)->name ?? 'Utente' }}</strong>
                                        <span class="text-muted" style="font-size: 0.75rem;">{{ $c->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="small">{{ $c->body }}</div>
                                </div>
                            @empty
                                <div class="text-muted small fst-italic text-center py-3">
                                    Nessun commento presente. Scrivi il primo!
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light fw-bold py-3">
                        Dettagli Task
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">

                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fw-bold small">Stato</span>
                                @php
                                    $statusBtnClass = match($task->status) {
                                        'done', 'completed' => 'bg-success',
                                        'in_progress', 'ongoing' => 'bg-warning text-dark',
                                        'open', 'todo' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $statusLabel = match($task->status) {
                                        'done', 'completed' => 'Completato',
                                        'in_progress', 'ongoing' => 'In Corso',
                                        'open', 'todo' => 'Da Fare',
                                        default => ucfirst($task->status)
                                    };
                                @endphp
                                <span class="badge {{ $statusBtnClass }} px-3 py-2 rounded-pill">
                                    {{ $statusLabel }}
                                </span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fw-bold small">Priorità</span>
                                @php
                                    $prioBtnClass = match($task->priority) {
                                        'high' => 'bg-danger',
                                        'medium' => 'bg-warning text-dark',
                                        'low' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                    $prioLabel = match($task->priority) {
                                        'high' => 'Alta',
                                        'medium' => 'Media',
                                        'low' => 'Bassa',
                                        default => 'N/D'
                                    };
                                @endphp
                                <span class="badge {{ $prioBtnClass }} px-3 py-2 rounded-pill">
                                    {{ $prioLabel }}
                                </span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted fw-bold small">Scadenza</span>
                                <span class="fw-bold">
                                    @if($task->due_date)
                                        {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted fst-italic">Nessuna</span>
                                    @endif
                                </span>
                            </li>

                            {{-- VISUALIZZA TAGS NEL DETTAGLIO --}}
                            <li class="list-group-item py-3">
                                <div class="text-muted fw-bold small mb-2">Tags</div>
                                <div>
                                    @if($task->tags && $task->tags->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($task->tags as $tag)
                                                <span class="badge bg-light text-dark border px-2 py-1">#{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic small">-- Nessun tag --</span>
                                    @endif
                                </div>
                            </li>

                            <li class="list-group-item py-3">
                                <div class="text-muted fw-bold small mb-1">Assegnato a</div>
                                <div>
                                    @if($task->user)
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="fw-bold text-primary">{{ $task->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">-- Nessun assegnatario --</span>
                                    @endif
                                </div>
                            </li>

                            <li class="list-group-item py-3">
                                <div class="text-muted fw-bold small mb-2">Collegato a</div>
                                <div>
                                    @if($task->milestone)
                                        <div class="mb-1">
                                            <span class="text-muted fw-bold">Milestone:</span>
                                            <span class="fw-bold text-primary">{{ str_replace('Milestone: ', '', $task->milestone->title) }}</span>
                                        </div>
                                    @elseif($task->project)
                                        <div>
                                            <span class="text-muted fw-bold">Progetto:</span>
                                            <a href="{{ route('projects.show', $task->project) }}" class="text-decoration-none fw-bold">
                                                {{ $task->project->title }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">-- Nessun collegamento --</span>
                                    @endif
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

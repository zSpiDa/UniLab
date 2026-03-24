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

    <a href="{{ route('projects.index') }}" class="btn btn-link p-0 mb-3 text-decoration-none">
        ← Torna ai progetti
    </a>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div class="w-100 me-3">
                    <h2 class="h4 mb-2">{{ $project->title }}</h2>
                    <div class="text-muted small mb-1">
                        <strong>Codice:</strong> {{ $project->code ?? 'n/d' }} |
                        <strong>Funder:</strong> {{ $project->funder ?? 'n/d' }}
                    </div>
                    <div class="text-muted small mb-2">
                        <strong>Periodo:</strong>
                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : '...' }}
                        →
                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') : '...' }}
                    </div>

                    <div class="mb-2">
                        Stato: <span class="badge bg-primary">{{ $project->status ? ($statusMap[$project->status] ?? ucfirst($project->status)) : 'n/d' }}</span>
                    </div>

                    @if($project->tags->isNotEmpty())
                        <div class="d-flex gap-1 flex-wrap mt-2 mb-3">
                            @foreach($project->tags as $t)
                                <span class="badge bg-info text-dark">#{{ $t->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($project->description))
                        <div class="mt-3 p-3 bg-light rounded border-start border-4 border-primary">
                            <h6 class="fw-bold mb-1">Descrizione del Progetto</h6>
                            <p class="mb-0 text-break" style="font-size: 0.95rem;">
                                {!! nl2br(e($project->description)) !!}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="{{ route('projects.export', $project) }}" class="btn btn-success btn-sm fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Esporta CSV
                    </a>
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning btn-sm fw-bold shadow-sm">
                        <i class="bi bi-pencil"></i> Modifica
                    </a>
                    <form id="delete-project-form" action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger btn-sm fw-bold shadow-sm" onclick="event.preventDefault(); if(confirm('Sei sicuro di voler eliminare questo progetto?')) { document.getElementById('delete-project-form').submit(); }">
                            <i class="bi bi-trash"></i> Elimina
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white fw-bold">Stato di Avanzamento</div>
        <div class="card-body">
            @php
                $totalTasks = $project->milestones->count();
                $completedTasks = $project->milestones->where('status', 'completed')->count();
                $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            @endphp
            <div class="mb-2">
                <strong>{{ $progress }}%</strong> completato ({{ $completedTasks }}/{{ $totalTasks }} milestone)
            </div>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $progress }}%
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    Milestone ({{ $project->milestones->count() }})
                </div>
                <div class="card-body">
                    @forelse($project->milestones as $m)
                        <div class="border-bottom py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $m->title }}</div>
                                <div class="text-muted small">
                                    Scadenza: {{ $m->due_date ? \Carbon\Carbon::parse($m->due_date)->format('d/m/Y') : 'n/d' }}
                                    <span class="ms-1 badge bg-light text-dark border">{{ $m->status ? ($statusMap[$m->status] ?? ucfirst($m->status)) : 'n/d' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small fst-italic">Nessuna milestone inserita.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    Pubblicazioni ({{ $project->publications->count() }})
                </div>
                <div class="card-body">
                    @forelse($project->publications as $pb)
                        <div class="border-bottom py-2">
                            <a href="{{ route('publications.show', $pb) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                {{ $pb->title }}
                            </a>
                            <div class="text-muted small">Status: {{ $pb->status ?? 'n/d' }}</div>
                        </div>
                    @empty
                        <div class="text-muted small fst-italic">Nessuna pubblicazione collegata.</div>
                    @endforelse
                </div>
            </div>
        </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Allegati</div>
                <div class="card-body">
                    @forelse($project->attachments as $a)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div class="text-truncate me-2">
                                {{ basename($a->path) }}
                                <div class="small text-muted">{{ $a->created_at->format('d/m/Y') }}</div>
                            </div>
                            <a href="{{ asset('storage/' . $a->path) }}" class="btn btn-sm btn-outline-primary fw-bold" download>
                                <i class="bi bi-download"></i> Scarica
                            </a>
                        </div>
                    @empty
                        <div class="text-muted small fst-italic">Nessun allegato.</div>
                    @endforelse
                </div>
            </div>
        </div>



        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span>Task del Progetto ({{ $project->tasks->count() }})</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Task</th>
                            <th>Tags</th> <th>Stato</th>
                            <th>Priorità</th>
                            <th>Assegnato a</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($project->tasks as $task)
                            <tr>
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}" class="fw-bold text-dark text-decoration-none d-block">
                                        {{ $task->title }}
                                    </a>
                                    @if($task->due_date)
                                        <small class="text-muted">Scadenza: {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}</small>
                                    @endif
                                </td>

                                {{-- I TAG ORA SONO IN QUESTA COLONNA DEDICATA --}}
                                <td>
                                    @if($task->tags && $task->tags->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($task->tags as $tag)
                                                <span class="badge bg-light text-dark border" style="font-size: 0.70rem;">#{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">--</span>
                                    @endif
                                </td>

                                <td>
                                    @php
                                        $statusBtnClass = match($task->status) {
                                            'done', 'completed' => 'btn-outline-success',
                                            'in_progress', 'ongoing' => 'btn-outline-warning',
                                            'open', 'todo' => 'btn-outline-danger',
                                            default => 'btn-outline-secondary'
                                        };
                                        $statusLabel = match($task->status) {
                                            'done', 'completed' => 'Completato',
                                            'in_progress', 'ongoing' => 'In Corso',
                                            'open', 'todo' => 'Da Fare',
                                            default => ucfirst($task->status)
                                        };
                                    @endphp
                                    <span class="btn btn-sm {{ $statusBtnClass }} fw-bold disabled py-0 px-2" style="opacity: 1; font-size: 0.75rem;">
                                    {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $prioBtnClass = match($task->priority) {
                                            'high' => 'btn-outline-danger',
                                            'medium' => 'btn-outline-warning',
                                            'low' => 'btn-outline-success',
                                            default => 'btn-outline-secondary'
                                        };
                                        $prioLabel = match($task->priority) {
                                            'high' => 'Alta',
                                            'medium' => 'Media',
                                            'low' => 'Bassa',
                                            default => 'N/D'
                                        };
                                    @endphp
                                    <span class="btn btn-sm {{ $prioBtnClass }} fw-bold disabled py-0 px-2" style="opacity: 1; font-size: 0.75rem;">
                                    {{ $prioLabel }}
                                    </span>
                                </td>
                                <td>
                                    @if($task->user)
                                        <span class="badge bg-info text-dark">{{ $task->user->name }}</span>
                                    @else
                                        <span class="text-muted small fst-italic">-- Nessuno --</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted fst-italic">
                                    Nessuna task associata a questo progetto.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    <div class="card mb-5 shadow-sm border-0">
        <div class="card-header bg-white fw-bold">
            Membri del Team
        </div>
        <div class="card-body">
            @forelse($project->users as $u)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <div class="fw-bold">{{ $u->name }}</div>
                        <div class="text-muted small">{{ $u->email }}</div>
                    </div>
                    <div>
                        <span class="badge bg-secondary">
                            {{ ucfirst($u->pivot->role ?? 'Membro') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-muted small fst-italic">Nessun membro assegnato a questo progetto.</div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteProjectModalLabel">Conferma Eliminazione</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Sei sicuro di voler eliminare definitivamente il progetto <strong>{{ $project->title }}</strong>?<br><br>
                    Questa operazione non può essere annullata e rimuoverà anche tutti gli allegati, i task e le milestone associati.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Sì, elimina progetto</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

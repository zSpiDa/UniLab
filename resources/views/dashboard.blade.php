@extends('layouts.app')
@section('content')
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Ciao, {{ $user->name }}</h1>
            <h4 class="text-muted">Ecco una panoramica delle tue attività recenti.</h4>
        </div>
    </div>

    {{-- Cards riepilogo --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-primary">{{ $projects->count() }}</div>
                    <div class="text-muted small">Progetti</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-success shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-success">{{ $assignedTasksCount }}</div>
                    <div class="text-muted small">Task totali</div>
                </div>
            </div>
        </div>
        {{-- CARD AGGIORNATA: Da Task in corso a Milestone --}}
        <div class="col-6 col-md-3">
            <div class="card text-center border-warning shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-warning">{{ $milestones->count() }}</div>
                    <div class="text-muted small">Milestone</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-info shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-info">{{ $publications->unique('id')->count() }}</div>
                    <div class="text-muted small">Pubblicazioni</div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRIGLIA CARD --}}
    <div class="row g-4">

        {{-- 1. Progetti associati (Sinistra) --}}
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Progetti associati</div>
                <div class="card-body p-0">
                    @forelse($projects as $project)
                        <a href="{{ route('projects.show', $project->id) }}" class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom text-decoration-none text-dark">
                            <div>
                                <div class="fw-semibold">{{ $project->title }}</div>
                                <small class="text-muted">{{ $project->code ?? '' }}</small>
                            </div>
                            <span class="badge text-bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'secondary' : 'warning') }}">
                                {{ ucfirst($project->status) }}
                            </span>
                        </a>
                    @empty
                        <div class="p-3 text-muted text-center">Non sei associato a nessun progetto.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 2. Milestone (Ora è a Destra, scambiata con Task) --}}
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Milestone dei tuoi Progetti</div>
                <div class="card-body p-0">
                    @forelse($milestones as $milestone)
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $milestone->title }}</div>
                                <small class="text-muted">{{ $milestone->project?->title ?? 'Nessun Progetto' }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $milestone->status === 'completed' || $milestone->status === 'done' ? 'success' : 'primary' }}">
                                    {{ ucfirst($milestone->status) }}
                                </span>
                                @if($milestone->due_date)
                                    <div class="small text-muted mt-1">
                                        {{ \Carbon\Carbon::parse($milestone->due_date)->format('d/m/Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-muted text-center">Nessuna milestone trovata.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 3. Task personali (Ora è in basso a sinistra) --}}
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Task personali</div>
                <div class="card-body p-0">
                    @forelse($myTasks as $task)
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ str_replace('Task: ', '', $task->title) }}</div>
                                <small class="text-muted">{{ $task->project?->title ?? 'Nessun Progetto' }}</small>
                            </div>
                            <div class="text-end">
                                {{-- Badge Status --}}
                                @php
                                    $statusBadgeClass = match($task->status) {
                                        'done', 'completed' => 'bg-success',
                                        'in_progress', 'ongoing' => 'bg-warning text-dark',
                                        'open', 'todo' => 'bg-danger',
                                        default       => 'bg-secondary'
                                    };
                                    $statusLabel = match($task->status) {
                                        'done', 'completed' => 'Completata',
                                        'in_progress', 'ongoing' => 'In Corso',
                                        'open', 'todo' => 'Da Fare',
                                        default       => ucfirst($task->status)
                                    };
                                @endphp
                                <span class="badge {{ $statusBadgeClass }}">
                                    {{ $statusLabel }}
                                </span>

                                {{-- Data Scadenza --}}
                                @if($task->due_date)
                                    <div class="small text-muted mt-1">
                                        {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-muted text-center">
                            Non hai task assegnate al momento.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 4. Pubblicazioni collegate --}}
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Pubblicazioni collegate</div>
                <div class="card-body p-0">
                    @forelse($publications->unique('id') as $pub)
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <div>
                                <a href="{{ route('publications.show', $pub) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                    {{ $pub->title }}
                                </a>
                                @php
                                    $authorNames = $pub->authors->map(function($author) {
                                        return $author->user?->name;
                                    })->filter()->join(', ');
                                @endphp
                                <small class="text-muted">{{ $authorNames ?: ($pub->author ?? 'Nessun autore assegnato') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-secondary">
                                    {{ ucfirst($pub->status ?? 'N/A') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-muted text-center">
                            Nessuna pubblicazione collegata ai tuoi progetti.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

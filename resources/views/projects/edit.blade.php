@extends('layouts.app')
@section('content')
    <a href="{{ route('projects.index') }}" class="btn btn-link p-0 mb-3">← Torna alla lista dei progetti</a>
    <div class="container">
        <h1>Modifica Progetto: {{ $project->title }}</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('projects.update', $project) }}" method="POST" class="mb-4" id="project-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3 mt-3">
                <label for="title" class="form-label"><b>Titolo del Progetto</b></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ $project->title }}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label"><b>Descrizione</b></label>
                <textarea name="description" id="description" class="form-control">{{ $project->description }}</textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="code" class="form-label"><b>Codice</b></label>
                    <input type="text" name="code" id="code" class="form-control"
                           value="{{ old('code', $project->code) }}"
                           placeholder="Es: PRJ-2026-001">
                </div>
                <div class="col-md-6">
                    <label for="funder" class="form-label"><b>Funder</b></label>
                    <input type="text" name="funder" id="funder" class="form-control"
                           value="{{ old('funder', $project->funder) }}"
                           placeholder="Es: Unione Europea, Regione, ecc...">
                </div>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label"><b>Stato</b></label>
                <select name="status" id="status" class="form-select" onchange="checkDateLimits()">
                    <option value="">Seleziona stato</option>
                    <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="ongoing" {{ $project->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="draft" {{ $project->status === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="start_date" class="form-label fw-bold">Data inizio</label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $project->start_date) }}" onchange="checkDateLimits()">
                    @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label fw-bold">Data fine</label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $project->end_date) }}" onchange="checkDateLimits()">
                    @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <script>
                function checkDateLimits() {
                    const statusSelect = document.getElementById('status');
                    const startDateInput = document.getElementById('start_date');
                    const endDateInput = document.getElementById('end_date');

                    if(!statusSelect || !startDateInput || !endDateInput) return;

                    const today = new Date().toISOString().split('T')[0];

                    // 1. REGOLE PER LA DATA DI INIZIO
                    if (statusSelect.value === 'draft') {
                        startDateInput.min = today;
                        if (startDateInput.value && startDateInput.value < today) {
                            startDateInput.value = '';
                        }
                    } else {
                        startDateInput.min = '';
                    }

                    // 2. REGOLE PER LA DATA DI FINE
                    let minEndDate = '';
                    if (statusSelect.value === 'ongoing') {
                        if (startDateInput.value && startDateInput.value > today) {
                            minEndDate = startDateInput.value;
                        } else {
                            minEndDate = today;
                        }
                    } else {
                        minEndDate = startDateInput.value;
                    }

                    endDateInput.min = minEndDate;

                    if (endDateInput.value && minEndDate && endDateInput.value < minEndDate) {
                        endDateInput.value = '';
                    }
                }

                document.addEventListener('DOMContentLoaded', checkDateLimits);
            </script>

            <div class="mb-3">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Milestones</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMilestone()">
                            + Aggiungi Milestone
                        </button>
                    </div>

                    <div id="milestones-container">
                        @foreach($project->milestones as $index => $milestone)
                            <div class="card mb-2 p-3 bg-light border milestone-row">
                                <input type="hidden" name="milestones[{{ $index }}][id]" value="{{ $milestone->id }}">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small text-muted">Titolo</label>
                                        <input type="text" name="milestones[{{ $index }}][title]" class="form-control" value="{{ $milestone->title }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Scadenza</label>
                                        <input type="date" name="milestones[{{ $index }}][due_date]" class="form-control" value="{{ $milestone->due_date ? \Carbon\Carbon::parse($milestone->due_date)->format('Y-m-d') : '' }}" onchange="checkMilestoneDate('{{ $index }}')">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Stato</label>
                                        <select name="milestones[{{ $index }}][status]" class="form-select" onchange="checkMilestoneDate('{{ $index }}')">
                                            <option value="planned" {{ $milestone->status == 'planned' ? 'selected' : '' }}>Pianificato</option>
                                            <option value="ongoing" {{ $milestone->status == 'ongoing' ? 'selected' : '' }}>In Corso</option>
                                            <option value="completed" {{ $milestone->status == 'completed' ? 'selected' : '' }}>Completato</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger w-100" onclick="removeMilestone(this)">
                                            <i class="fas fa-trash"></i> X
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <script>
                    let milestoneIndex = {{ $project->milestones->count() }};

                    function checkMilestoneDate(index) {
                        const statusSelect = document.querySelector(`select[name="milestones[${index}][status]"]`);
                        const dueDateInput = document.querySelector(`input[name="milestones[${index}][due_date]"]`);

                        if (!statusSelect || !dueDateInput) return;

                        const today = new Date().toISOString().split('T')[0];

                        // Nelle milestone gli stati sono "planned" e "ongoing"
                        if (statusSelect.value === 'planned' || statusSelect.value === 'ongoing') {
                            dueDateInput.min = today;
                            if (dueDateInput.value && dueDateInput.value < today) {
                                dueDateInput.value = '';
                            }
                        } else {
                            dueDateInput.min = '';
                        }
                    }

                    function addMilestone() {
                        const container = document.getElementById('milestones-container');
                        const newIndexStr = 'new_' + milestoneIndex; // Creiamo una stringa come 'new_0'

                        const row = document.createElement('div');
                        row.className = 'row g-2 mb-2 align-items-end p-3 bg-light border rounded milestone-row';

                        row.innerHTML = `
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">Titolo</label>
                                <input type="text" class="form-control" name="milestones[${newIndexStr}][title]" placeholder="Nuova Milestone" required>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted">Data Scadenza</label>
                                <input type="date" class="form-control" name="milestones[${newIndexStr}][due_date]" onchange="checkMilestoneDate('${newIndexStr}')">
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted">Stato</label>
                                <select class="form-select" name="milestones[${newIndexStr}][status]" onchange="checkMilestoneDate('${newIndexStr}')">
                                    <option value="planned" selected>Pianificato</option>
                                    <option value="ongoing">In Corso</option>
                                    <option value="completed">Completato</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger w-100 fw-bold" onclick="removeMilestone(this)">X</button>
                            </div>
                        `;
                        container.appendChild(row);

                        // Applica il blocco "oggi" da subito alla nuova milestone creata
                        checkMilestoneDate(newIndexStr);

                        milestoneIndex++;
                    }

                    function removeMilestone(button) {
                        button.closest('.milestone-row').remove();
                    }

                    // Controlla le milestone esistenti al caricamento della pagina
                    document.addEventListener('DOMContentLoaded', function() {
                        @foreach($project->milestones as $index => $milestone)
                        checkMilestoneDate('{{ $index }}');
                        @endforeach
                    });
                </script>

                <div class="mb-3">
                    <h5>Allegati</h5>
                    @if($project->attachments->count() > 0)
                        <label class="form-label text-muted small">Allegati esistenti (Seleziona per rimuovere):</label>
                        <ul class="list-group mb-3">
                            @foreach($project->attachments as $attachment)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                        {{ $attachment->name ?? basename($attachment->path) }}
                                    </a>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="delete_attachments[]" value="{{ $attachment->id }}" id="del_att_{{ $attachment->id }}">
                                        <label class="form-check-label text-danger" for="del_att_{{ $attachment->id }}">Rimuovi</label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small">Nessun allegato presente.</p>
                    @endif
                    <div class="mb-2">
                        <label for="file" class="form-label">Aggiungi nuovo allegato (PDF)</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".pdf">
                    </div>
                </div>

                <div class="mb-3">
                    <h5>Membri del Progetto</h5>
                    <div id="members-container" class="mb-3">
                        @php
                            $currentUsersIds = old('users') ? old('users') : $project->users->pluck('id')->toArray();
                        @endphp

                        @foreach($users as $user)
                            @if(in_array($user->id, $currentUsersIds))
                                <div class="d-flex justify-content-between align-items-center border p-2 mb-2 bg-white rounded member-row">
                                    <span>{{ $user->name }} <small class="text-muted">({{ $user->role ?? 'Utente' }})</small></span>
                                    <input type="hidden" name="users[]" value="{{ $user->id }}">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.member-row').remove()">Rimuovi</button>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="input-group">
                        <select id="user-select" class="form-select">
                            <option value="">-- Seleziona utente da aggiungere --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" data-role="{{ $user->role ?? 'Utente' }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary" onclick="addMember()">Aggiungi Membro</button>
                    </div>
                </div>

                <script>
                    function addMember() {
                        const select = document.getElementById('user-select');
                        const userId = select.value;
                        const userName = select.options[select.selectedIndex].text;
                        if (!userId) return;

                        if (document.querySelector(`input[name="users[]"][value="${userId}"]`)) {
                            alert('Questo utente è già stato aggiunto!');
                            return;
                        }

                        const container = document.getElementById('members-container');
                        const memberRow = document.createElement('div');
                        memberRow.className = 'd-flex justify-content-between align-items-center border p-2 mb-2 bg-white rounded member-row';
                        memberRow.innerHTML = `
                            <span>${userName}</span>
                            <input type="hidden" name="users[]" value="${userId}">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.member-row').remove()">Rimuovi</button>
                        `;

                        container.appendChild(memberRow);
                        select.value = '';
                    }
                </script>
            </div>

            <button type="submit" class="btn btn-primary mb-5">Salva Modifiche al Progetto</button>
        </form>

        {{-- INIZIO SEZIONE TASK --}}
        <div class="card mb-5">
            <div class="card-header fw-bold">
                Crea Nuova Task
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Titolo Task</label>
                            <input type="text" name="title" class="form-control" placeholder="Es: Analisi dati preliminari..." required>
                        </div>

                        <div class="col-md-6">
                            <label for="target" class="form-label fw-bold">Associa a (Progetto / Milestone)</label>
                            <select name="target" id="target" class="form-select @error('target') is-invalid @enderror" required>
                                <option value="">-- Seleziona --</option>
                                <optgroup label="Progetto: {{ $project->title }}">
                                    <option value="project_{{ $project->id }}">
                                        --> Assegna solo al Progetto (Nessuna Milestone)
                                    </option>
                                    @foreach($project->milestones as $m)
                                        <option value="milestone_{{ $m->id }}">
                                            &nbsp;&nbsp;&nbsp;↳ Milestone: {{ $m->title }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assegna a (Utente)</label>
                            <select name="assignee_id" class="form-select">
                                <option value="">-- Nessuno --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stato</label>
                            <select name="status" id="new_task_status" class="form-select" onchange="checkNewTaskDateLimits()">
                                <option value="open" selected>Da Fare</option>
                                <option value="in_progress">In Corso</option>
                                <option value="done">Completato</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Priorità</label>
                            <select name="priority" class="form-select">
                                <option value="low">Bassa</option>
                                <option value="medium" selected>Media</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Scadenza</label>
                            <input type="date" name="due_date" id="new_task_due_date" class="form-control" onchange="checkNewTaskDateLimits()">
                        </div>

                        <script>
                            function checkNewTaskDateLimits() {
                                const statusSelect = document.getElementById('new_task_status');
                                const dueDateInput = document.getElementById('new_task_due_date');

                                if (!statusSelect || !dueDateInput) return;

                                const today = new Date().toISOString().split('T')[0];

                                if (statusSelect.value === 'open' || statusSelect.value === 'in_progress') {
                                    dueDateInput.min = today;

                                    if (dueDateInput.value && dueDateInput.value < today) {
                                        dueDateInput.value = '';
                                    }
                                } else {
                                    dueDateInput.min = '';
                                }
                            }

                            document.addEventListener('DOMContentLoaded', checkNewTaskDateLimits);
                        </script>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Descrizione (opzionale)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Dettagli aggiuntivi..."></textarea>
                        </div>

                        <div class="col-md-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Crea Task
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-3">
            <h5>Task Associate al Progetto</h5>
            @if($project->tasks->count() > 0)
                <ul class="list-group">
                    @foreach($project->tasks as $task)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $task->title }}</strong><br>
                                <small class="text-muted">{{ $task->user?->name ?? 'Non assegnato' }}</small>
                                @if($task->milestone)
                                    <span class="badge bg-secondary ms-2">Milestone: {{ $task->milestone->title }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center" style="gap: 10px;">
                                <form method="POST" action="{{ route('tasks.update', $task) }}" class="m-0 p-0">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="title" value="{{ $task->title }}">
                                    <input type="hidden" name="description" value="{{ $task->description }}">
                                    <input type="hidden" name="due_date" value="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="priority" value="{{ $task->priority }}">

                                    <input type="hidden" name="target" value="{{ $task->milestone_id ? 'milestone_'.$task->milestone_id : 'project_'.$project->id }}">
                                    <select name="status" class="form-select form-select-sm m-0" style="width: auto; min-width: 130px;" onchange="this.form.submit()">
                                        <option value="open" {{ $task->status == 'open' ? 'selected' : '' }}>Da Fare</option>
                                        <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Corso</option>
                                        <option value="done" {{ $task->status == 'done' ? 'selected' : '' }}>Completato</option>
                                    </select>
                                </form>

                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="m-0 p-0" onsubmit="return confirm('Sei sicuro di voler eliminare questa task?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm m-0">
                                        Elimina
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Nessuna task associata a questo progetto.</p>
            @endif
        </div>

        <div class="mb-3 border-top pt-4 mt-4">
            <h5>Pubblicazioni</h5>
            @if($project->publications->count() > 0)
                <ul class="list-group">
                    @foreach($project->publications as $pub)
                        <li class="list-group-item">
                            <strong>{{ $pub->title }}</strong><br>
                            @php
                                $authorNames = $pub->authors->map(function($author) {
                                    return $author->user?->name;
                                })->filter()->join(', ');
                            @endphp
                            <small class="text-muted">Autori: {{ $authorNames ?: ($pub->author ?? 'Non specificato') }}</small>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Nessuna pubblicazione associata a questo progetto.</p>
            @endif
        </div>
    </div>
@endsection

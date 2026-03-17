@extends('layouts.app')
@section('content')
    <!--prova -->
    <!--prova -->
    <a href="{{ route('projects.index') }}" class="btn btn-link p-0 mb-3">← Torna alla lista dei progetti</a>
    <div class="container">
        <h1>Modifica Progetto: {{ $project->title }}</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('projects.update', $project) }}" method="POST" class="mb-4" enctype="multipart/form-data">
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
                <select name="status" id="status" class="form-select">
                    <option value="">Seleziona stato</option>
                    <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="ongoing" {{ $project->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="draft" {{ $project->status === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="mb-3">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Milestones</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMilestone()">
                            + Aggiungi Milestone
                        </button>
                    </div>

                    <div id="milestones-container">
                        {{-- Loop per le milestone esistenti --}}
                        @foreach($project->milestones as $index => $milestone)
                            <div class="card mb-2 p-3 bg-light border milestone-row">
                                {{-- ID fondamentale per l'aggiornamento. Se rimosso, il controller cancellerà la milestone --}}
                                <input type="hidden" name="milestones[{{ $index }}][id]" value="{{ $milestone->id }}">

                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small text-muted">Titolo</label>
                                        <input type="text" name="milestones[{{ $index }}][title]" class="form-control" value="{{ $milestone->title }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Scadenza</label>
                                        <input type="date" name="milestones[{{ $index }}][due_date]" class="form-control" value="{{ $milestone->due_date ? \Carbon\Carbon::parse($milestone->due_date)->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Stato</label>
                                        <select name="milestones[{{ $index }}][status]" class="form-select">
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
                    // Inizializziamo il contatore basandoci sul numero attuale di milestone per evitare conflitti di indici
                    let milestoneIndex = {{ $project->milestones->count() }};

                    function addMilestone() {
                        const container = document.getElementById('milestones-container');
                        const newIndex = milestoneIndex++; // Incrementa l'indice per la nuova riga

                        const html = `
                            <div class="card mb-2 p-3 bg-light border milestone-row">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small text-muted">Titolo</label>
                                        <input type="text" name="milestones[new_${newIndex}][title]" class="form-control" placeholder="Nuova Milestone" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Scadenza</label>
                                        <input type="date" name="milestones[new_${newIndex}][due_date]" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Stato</label>
                                        <select name="milestones[new_${newIndex}][status]" class="form-select">
                                            <option value="planned" selected>Planned</option>
                                            <option value="ongoing">Ongoing</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger w-100" onclick="removeMilestone(this)">
                                            X
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Aggiunge l'HTML al contenitore
                        container.insertAdjacentHTML('beforeend', html);
                    }

                    function removeMilestone(button) {
                        button.closest('.milestone-row').remove();
                    }
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
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
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
                        const userRole = select.options[select.selectedIndex].getAttribute('data-role');
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
        <div class="card mb-5">
            <div class="card-header fw-bold">
                Crea Nuova Task
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <input type="hidden" name="target" value="project_{{ $project->id }}">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Titolo Task</label>
                            <input type="text" name="title" class="form-control" placeholder="Es: Analisi dati preliminari..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assegna a</label>
                            <select name="assignee_id" class="form-select">
                                <option value="">-- Nessuno --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Stato</label>
                            <select name="status" class="form-select">
                                <option value="open" selected>Da Fare</option>
                                <option value="in_progress">In Corso</option>
                                <option value="done">Completato</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Priorità</label>
                            <select name="priority" class="form-select">
                                <option value="low">Bassa</option>
                                <option value="medium" selected>Media</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Scadenza</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>

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

        {{-- SEZIONE TAG AGGIORNATA --}}
        <div class="mb-4 border-t pt-4">
            <h5>Tag del Progetto</h5>
            <div class="mb-3">
                <label for="tags" class="form-label small text-muted">Inserisci i tag separati da virgola o premi invio</label>
                {{-- Usiamo l'implode per mostrare i tag esistenti nel campo di testo --}}
                <input type="text" name="tags" id="tags-input" class="form-control"
                       value="{{ old('tags', $project->tags->pluck('name')->implode(',')) }}"
                       placeholder="Aggiungi tag...">
            </div>
        </div>
        <div class="mb-3">
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

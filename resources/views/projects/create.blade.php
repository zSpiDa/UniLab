@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">Crea nuovo progetto</h1>

        <form action="{{ route('projects.store') }}" enctype="multipart/form-data" method="POST">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-3">
                <label for="title" class="form-label fw-bold">Titolo</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label fw-bold">Stato</label>
                <select class="form-select" id="status" name="status" onchange="checkEndDateLimit()">
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Pianificato (Draft)</option>
                    <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>In corso (Ongoing)</option>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Completato (Active)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="code" class="form-label fw-bold">Codice</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}">
            </div>

            <div class="mb-3">
                <label for="funder" class="form-label fw-bold">Funder</label>
                <input type="text" class="form-control" id="funder" name="funder" value="{{ old('funder') }}">
            </div>

            <div class="mb-3">
                <label for="start_date" class="form-label fw-bold">Data inizio</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}" onchange="checkEndDateLimit()">
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label fw-bold">Data fine</label>
                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <script>
                function checkEndDateLimit() {
                    const statusSelect = document.getElementById('status');
                    const startDateInput = document.getElementById('start_date');
                    const endDateInput = document.getElementById('end_date');

                    const today = new Date().toISOString().split('T')[0];
                    let minDate = '';

                    if (statusSelect.value === 'ongoing') {
                        if (startDateInput.value && startDateInput.value > today) {
                            minDate = startDateInput.value;
                        } else {
                            minDate = today;
                        }
                    } else {
                        minDate = startDateInput.value;
                    }

                    endDateInput.min = minDate;

                    if (endDateInput.value && minDate && endDateInput.value < minDate) {
                        endDateInput.value = '';
                    }
                }
                document.addEventListener('DOMContentLoaded', checkEndDateLimit);
            </script>

            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Descrizione</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4 p-3 bg-light rounded border">
                <h5 class="form-label fw-bold">Membri del Progetto</h5>

                <div id="members-container" class="mb-3">
                    @if(old('users'))
                        @foreach($users as $user)
                            @if(in_array($user->id, old('users')))
                                <div class="d-flex justify-content-between align-items-center border p-2 mb-2 bg-white rounded shadow-sm member-row">
                                    <span><span class="fw-bold">{{ $user->name }}</span> <span class="text-muted small">({{ $user->role ?? 'Utente' }})</span></span>
                                    <input type="hidden" name="users[]" value="{{ $user->id }}">
                                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3" onclick="this.closest('.member-row').remove()">Rimuovi</button>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <div class="input-group">
                    <select id="user-select" class="form-select">
                        <option value="">-- Seleziona utente da aggiungere --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" data-role="{{ $user->role ?? 'Utente' }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary fw-bold" onclick="addMember()">Aggiungi Membro</button>
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
                    memberRow.className = 'd-flex justify-content-between align-items-center border p-2 mb-2 bg-white rounded shadow-sm member-row';
                    memberRow.innerHTML = `
                        <span><span class="fw-bold">${userName}</span> <span class="text-muted small">(${userRole})</span></span>
                        <input type="hidden" name="users[]" value="${userId}">
                        <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3" onclick="this.closest('.member-row').remove()">Rimuovi</button>
                    `;

                    container.appendChild(memberRow);
                    select.value = '';
                }
            </script>

            <div class="mb-4">
                <h5 class="fw-bold">Milestone</h5>
                <div id="milestones-container">
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold mb-3" onclick="addMilestone()">
                        + Aggiungi milestone
                    </button>
                </div>
                <script>
                    let milestoneIndex = 0;
                    function addMilestone() {
                        const container = document.getElementById('milestones-container');
                        const row = document.createElement('div');
                        row.className = 'row g-2 mb-2 align-items-end p-3 bg-light border rounded';
                        row.innerHTML = `
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">Titolo</label>
                                <input type="text" class="form-control" name="milestones[${milestoneIndex}][title]" placeholder="Titolo">
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted">Data Scadenza</label>
                                <input type="date" class="form-control" name="milestones[${milestoneIndex}][due_date]">
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted">Stato</label>
                                <select class="form-select" name="milestones[${milestoneIndex}][status]">
                                    <option value="draft">Pianificato (Draft)</option>
                                    <option value="ongoing">In corso (Ongoing)</option>
                                    <option value="active">Completato (Active)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 fw-bold" onclick="this.closest('.row').remove()">Rimuovi</button>
                            </div>
                        `;
                        container.appendChild(row);
                        milestoneIndex++;
                    }
                </script>
            </div>

            {{-- ----------------------------------------------------- --}}
            {{-- SEZIONE: TASK DINAMICHE                               --}}
            {{-- ----------------------------------------------------- --}}
            <div class="card mb-4 border shadow-sm">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    Crea Task Iniziali per il Progetto
                    <button type="button" class="btn btn-sm btn-primary" onclick="addTask()">
                        + Aggiungi Task
                    </button>
                </div>
                <div class="card-body bg-light" id="tasks-container">
                    <p class="text-muted small mb-0" id="tasks-placeholder">Nessuna task aggiunta. Clicca su "+ Aggiungi Task" per crearne una.</p>
                </div>
            </div>

            <script>
                let taskIndex = 0;
                // Pre-renderizziamo gli utenti per i menu a tendina delle task
                const taskUsersOptions = `@foreach($users as $u)<option value="{{ $u->id }}">{{ str_replace("'", "\'", $u->name) }}</option>@endforeach`;

                function addTask() {
                    const placeholder = document.getElementById('tasks-placeholder');
                    if(placeholder) placeholder.style.display = 'none';

                    const container = document.getElementById('tasks-container');
                    const newIndex = taskIndex++;

                    const html = `
                        <div class="card mb-3 p-3 bg-white border task-row">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Titolo Task</label>
                                    <input type="text" name="tasks[new_${newIndex}][title]" class="form-control" placeholder="Es: Analisi dati preliminari..." required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Assegna a</label>
                                    <select name="tasks[new_${newIndex}][assignee_id]" class="form-select">
                                        <option value="">-- Nessuno --</option>
                                        ${taskUsersOptions}
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Stato</label>
                                    <select name="tasks[new_${newIndex}][status]" class="form-select">
                                        <option value="open" selected>Da Fare</option>
                                        <option value="in_progress">In Corso</option>
                                        <option value="done">Completato</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Priorità</label>
                                    <select name="tasks[new_${newIndex}][priority]" class="form-select">
                                        <option value="low">Bassa</option>
                                        <option value="medium" selected>Media</option>
                                        <option value="high">Alta</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Scadenza</label>
                                    <input type="date" name="tasks[new_${newIndex}][due_date]" class="form-control">
                                </div>

                                <div class="col-md-11">
                                    <label class="form-label">Descrizione (opzionale)</label>
                                    <textarea name="tasks[new_${newIndex}][description]" class="form-control" rows="1" placeholder="Dettagli aggiuntivi..."></textarea>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger w-100" onclick="removeTask(this)">
                                        X
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                }

                function removeTask(button) {
                    button.closest('.task-row').remove();
                    if(document.querySelectorAll('.task-row').length === 0) {
                        document.getElementById('tasks-placeholder').style.display = 'block';
                    }
                }
            </script>

            <div class="mb-3 mt-4">
                <label for="tags" class="form-label fw-bold">Tags</label>
                <input type="text" placeholder="Inserisci i tag separati da virgola (es. 'Biologia, Chimica')" class="form-control" id="tags" name="tags" value="{{ old('tags') }}">
            </div>

            <div class="mb-4">
                <label for="file" class="form-label fw-bold">Allega file PDF</label>
                <input type="file" class="form-control" id="file" name="file" accept=".pdf">
            </div>

            <button type="submit" class="btn btn-primary">Crea progetto</button>
        </form>
    </div>
@endsection

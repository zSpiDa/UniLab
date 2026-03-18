@extends('layouts.app')

@section('content')
    @php
        $statusMap = [
            'planned'   => 'Pianificato',
            'ongoing'   => 'In Corso',
            'completed' => 'Completato',
        ];
    @endphp

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
                <select class="form-select" id="status" name="status" onchange="checkDateLimits()">
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Pianificato</option>
                    <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>In corso</option>
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completato</option>
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
                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" onchange="checkDateLimits()">
                @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label fw-bold">Data fine</label>
                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" onchange="checkDateLimits()">
                @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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

                    // Nuova funzione per controllare la data della singola Milestone
                    function checkMilestoneDate(index) {
                        const statusSelect = document.querySelector(`select[name="milestones[${index}][status]"]`);
                        const dueDateInput = document.querySelector(`input[name="milestones[${index}][due_date]"]`);

                        if (!statusSelect || !dueDateInput) return;

                        const today = new Date().toISOString().split('T')[0];

                        if (statusSelect.value === 'draft' || statusSelect.value === 'ongoing') {
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
                        const row = document.createElement('div');
                        row.className = 'row g-2 mb-2 align-items-end p-3 bg-light border rounded';

                        // Passiamo il milestoneIndex corrente alla funzione checkMilestoneDate tramite onchange
                        row.innerHTML = `
                            <div class="col-md-4">
                                <label class="small fw-bold text-muted">Titolo</label>
                                <input type="text" class="form-control" name="milestones[${milestoneIndex}][title]" placeholder="Titolo">
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted">Data Scadenza</label>
                                <input type="date" class="form-control" name="milestones[${milestoneIndex}][due_date]" onchange="checkMilestoneDate(${milestoneIndex})">
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted">Stato</label>
                                <select class="form-select" name="milestones[${milestoneIndex}][status]" onchange="checkMilestoneDate(${milestoneIndex})">
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

                        // Chiamiamo subito la funzione per impostare il limite "oggi" di default sulla nuova milestone
                        checkMilestoneDate(milestoneIndex);

                        milestoneIndex++;
                    }
                </script>
            </div>

            <button type="submit" class="btn btn-primary">Crea progetto</button>
        </form>
    </div>
@endsection

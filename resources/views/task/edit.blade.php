@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <div class="card-header fw-bold">
                        Modifica Task: {{ str_replace('Task: ', '', $task->title) }}
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('tasks.update', $task) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">Titolo Task</label>
                                <input type="text"
                                       class="form-control @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title', str_replace('Task: ', '', $task->title)) }}"
                                       required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">Descrizione</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="3">{{ old('description', $task->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="target" class="form-label fw-bold">Progetto / Milestone</label>
                                <select name="target" id="target" class="form-select @error('target') is-invalid @enderror">
                                    <option value="">-- Nessun collegamento --</option>

                                    @php
                                        // Calcoliamo cosa deve essere selezionato di default
                                        $currentValue = old('target', $task->milestone_id ? 'milestone_'.$task->milestone_id : ($task->project_id ? 'project_'.$task->project_id : ''));
                                    @endphp

                                    @foreach($projects as $project)
                                        <optgroup label="Progetto: {{ $project->title }}">

                                            <option value="project_{{ $project->id }}" {{ $currentValue == 'project_'.$project->id ? 'selected' : '' }}>
                                                --> Assegna solo al Progetto
                                            </option>

                                            @foreach($project->milestones as $m)
                                                <option value="milestone_{{ $m->id }}" {{ $currentValue == 'milestone_'.$m->id ? 'selected' : '' }}>
                                                    &nbsp;&nbsp;&nbsp;↳ Milestone: {{ $m->title }}
                                                </option>
                                            @endforeach

                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="due_date" class="form-label fw-bold">Scadenza</label>
                                    <input type="date"
                                           class="form-control @error('due_date') is-invalid @enderror"
                                           id="due_date"
                                           name="due_date"
                                           value="{{ old('due_date', $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '') }}">
                                    @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label fw-bold">Stato</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="open" {{ old('status', $task->status) == 'open' ? 'selected' : '' }}>Da Fare</option>
                                        <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Corso</option>
                                        <option value="done" {{ old('status', $task->status) == 'done' ? 'selected' : '' }}>Completato</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label fw-bold">Priorità</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                        <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Bassa</option>
                                        <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Media</option>
                                        <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>Alta</option>
                                    </select>
                                    @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="assignee_id" class="form-label fw-bold">Assegnato a</label>
                                    <select class="form-select @error('assignee_id') is-invalid @enderror" id="assignee_id" name="assignee_id" required>
                                        <option value="">-- Seleziona Utente --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('assignee_id', $task->assignee_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assignee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('tasks.index') }}" class="btn btn-secondary fw-bold">Annulla</a>
                                <button type="submit" class="btn btn-primary fw-bold">Salva Modifiche</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header fw-bold">
                        Gestione Commenti
                    </div>
                    <div class="card-body">
                        @if($task->comments && $task->comments->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($task->comments as $c)
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="small text-primary">{{ optional($c->user)->name ?? 'Utente' }}</strong>
                                            <small class="text-muted ms-1">({{ $c->created_at->format('d/m/Y H:i') }})</small>
                                            <div class="mt-1 small">{{ $c->body }}</div>
                                        </div>

                                        @if(auth()->id() === $c->user_id)
                                            <form action="{{ route('comments.destroy', $c->id) }}" method="POST" class="m-0 p-0" onsubmit="return confirm('Sei sicuro di voler eliminare questo commento?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Elimina
                                                </button>
                                            </form>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted small fst-italic mb-0">Nessun commento presente per questa task.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')
<!-- modifica della pubblicazione, simile alla creazione, ma con i campi precompilati -->
@section('content')
<div class="container">
    <h1>Modifica Pubblicazione</h1>
    <form action="{{ route('publications.update', $publication->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Titolo</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ $publication->title }}" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Tipo di pubblicazione</label>
            <input type="text" class="form-control" id="type" name="type" value="{{ $publication->type }}" required>
        </div>
        <div class="mb-3">
            <label for="venue" class="form-label">Luogo di pubblicazione</label>
            <input type="text" class="form-control" id="venue" name="venue" value="{{ $publication->venue }}" required>
        </div>
        <div class="mb-3">
            <label for="doi" class="form-label">DOI</label>
            <input type="text" class="form-control" id="doi" name="doi" value="{{ $publication->doi }}" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Stato</label>
            <select class="form-select" id="status" name="status" required>
                <option value="">Seleziona uno stato</option>
                <option value="drafting" {{ $publication->status == 'drafting' ? 'selected' : '' }}>Bozza</option>
                <option value="submitted" {{ $publication->status == 'submitted' ? 'selected' : '' }}>Inviato</option>
                <option value="accepted" {{ $publication->status == 'accepted' ? 'selected' : '' }}>Accettato</option>
                <option value="published" {{ $publication->status == 'published' ? 'selected' : '' }}>Pubblicato</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="target_deadline" class="form-label">Deadline della pubblicazione</label>
            <input type="date" class="form-control" id="target_deadline" name="target_deadline" value="{{ $publication->target_deadline ?? '' }}">
        </div>
        <div class="mb-3">
            <label for="project_id" class="form-label">Progetto associato</label>
            <h2 class="text-muted small">Seleziona uno o più progetti associati a questa pubblicazione: </h2>
            <select class="form-select" id="projects" name="projects[]" required multiple>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ in_array($project->id, $publication->projects->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $project->title }}</option>
                @endforeach
            </select>
        </div>
        <!-- autori associati alla pubblicazione -->
        <div class="mb-3">
            <h5>Autori associati</h5>
            <div id="authors-container">
                @foreach($publication->authors->sortBy('order') as $idx => $author)
                    <div class="author-row d-flex gap-2 mb-2 align-items-center">
                        <select name="authors[user_id][]" class="form-select">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $author->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="authors[order][]" class="form-control" min="1" value="{{ $author->order }}" style="width:100px">
                        <label class="d-flex align-items-center gap-1 text-nowrap">
                            <input type="checkbox" name="authors[is_corresponding][{{ $idx }}]" value="1" {{ $author->is_corresponding ? 'checked' : '' }}> Corr.
                        </label>
                        <button type="button" class="btn btn-danger btn-sm remove-author">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-author" class="btn btn-secondary btn-sm mt-2">+ Aggiungi autore</button>
        </div>
        <button type="submit" class="btn btn-primary">Aggiorna Pubblicazione</button>
    </form>
</div>

<script>
document.getElementById('add-author').addEventListener('click', function() {
    var container = document.getElementById('authors-container');
    var rows = container.querySelectorAll('.author-row');
    var newIndex = rows.length;

    var row = document.createElement('div');
    row.className = 'author-row d-flex gap-2 mb-2 align-items-center';

    var firstSelect = rows.length > 0 ? rows[0].querySelector('select') : null;
    var selectClone;
    if (firstSelect) {
        selectClone = firstSelect.cloneNode(true);
        selectClone.value = '';
    } else {
        selectClone = document.createElement('select');
        selectClone.name = 'authors[user_id][]';
        selectClone.className = 'form-select';
        @foreach($users as $user)
            var opt = document.createElement('option');
            opt.value = '{{ $user->id }}';
            opt.textContent = '{{ $user->name }}';
            selectClone.appendChild(opt);
        @endforeach
    }
    row.appendChild(selectClone);

    var orderInput = document.createElement('input');
    orderInput.type = 'number';
    orderInput.name = 'authors[order][]';
    orderInput.className = 'form-control';
    orderInput.min = '1';
    orderInput.value = newIndex + 1;
    orderInput.style.width = '100px';
    row.appendChild(orderInput);

    var label = document.createElement('label');
    label.className = 'd-flex align-items-center gap-1 text-nowrap';
    var checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.name = 'authors[is_corresponding][' + newIndex + ']';
    checkbox.value = '1';
    label.appendChild(checkbox);
    label.appendChild(document.createTextNode(' Corr.'));
    row.appendChild(label);

    var removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'btn btn-danger btn-sm remove-author';
    removeBtn.textContent = '✕';
    removeBtn.addEventListener('click', function() { row.remove(); });
    row.appendChild(removeBtn);

    container.appendChild(row);
});

document.querySelectorAll('.remove-author').forEach(function(btn) {
    btn.addEventListener('click', function() {
        this.closest('.author-row').remove();
    });
});
</script>
@endsection

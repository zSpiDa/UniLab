@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">Crea una nuova pubblicazione</h1>

        <form action="{{ route('publications.store') }}" method="POST" class="card p-4 shadow-sm border-0">
            @csrf

            <div class="mb-3">
                <label for="title" class="form-label"><strong>Titolo</strong></label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label"><strong>Tipo di pubblicazione</strong></label>
                    <input type="text" class="form-control" id="type" name="type" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="venue" class="form-label"><strong>Luogo di pubblicazione (Venue)</strong></label>
                    <input type="text" class="form-control" id="venue" name="venue" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="doi" class="form-label"><strong>DOI</strong></label>
                    <input type="text" class="form-control" id="doi" name="doi" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="target_deadline" class="form-label"><strong>Deadline della pubblicazione</strong></label>
                    <input type="date" class="form-control" id="target_deadline" name="target_deadline" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="status" class="form-label"><strong>Stato</strong></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="drafting">Bozza</option>
                    <option value="submitted">Inviato</option>
                    <option value="accepted">Accettato</option>
                    <option value="published">Pubblicato</option>
                </select>
            </div>

            <div class="mb-4 p-3 bg-light rounded border">
                <label for="projects" class="form-label"><strong>Progetti associati</strong></label>
                <p class="text-muted small mb-2">Seleziona uno o più progetti. <em>(Tieni premuto CTRL su Windows o CMD su Mac per selezionarne multipli)</em></p>
                <select class="form-select" id="projects" name="projects[]" required multiple size="4">
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4 p-3 bg-light rounded border">
                <label class="form-label d-block"><strong>Autori associati</strong></label>

                <div id="authors-container">
                    <div class="author-row d-flex align-items-center gap-2 mb-2">
                        <select name="authors[user_id][]" class="form-select">
                            <option value="">Seleziona autore</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="authors[order][]" class="form-control" placeholder="Ordine" min="1" value="1" style="width:100px">
                        <div class="form-check ms-2 me-2">
                            <input class="form-check-input" type="checkbox" name="authors[is_corresponding][0]" value="1">
                            <label class="form-check-label">Corr.</label>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-author">✕</button>
                    </div>
                </div>

                <button
                    type="button" id="add-author" class="btn btn-sm btn-outline-primary mt-2">+ Aggiungi autore</button>
            </div>

            <div class="mb-3">
                <h5 class="fw-bold">PDF principale</h5>
                <div class="mb-2">
                    <label for="file" class="form-label">Aggiungi allegato </label>
                    <input type="file" name="file" id="file" class="form-control" accept=".pdf">
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary btn-lg fw-bold px-5">Crea pubblicazione</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let authorIndex = 1; // Contatore per gli indici univoci delle checkbox

            document.getElementById('add-author').addEventListener('click', function() {
                const container = document.getElementById('authors-container');
                const rows = container.querySelectorAll('.author-row');
                const newOrderValue = rows.length + 1;

                // Creiamo la nuova riga
                const row = document.createElement('div');
                row.className = 'author-row d-flex align-items-center gap-2 mb-2';

                // Cloniamo la select dalla prima riga (se esiste, altrimenti creiamo tutto da zero)
                const firstSelect = rows[0] ? rows[0].querySelector('select') : null;
                let selectHtml = '';
                if (firstSelect) {
                    const selectClone = firstSelect.cloneNode(true);
                    selectClone.value = ''; // Resetta il valore
                    selectHtml = selectClone.outerHTML;
                }

                row.innerHTML = `
            ${selectHtml}
            <input type="number" name="authors[order][]" class="form-control" placeholder="Ordine" min="1" value="${newOrderValue}" style="width:100px">
            <div class="form-check ms-2 me-2">
                <input class="form-check-input" type="checkbox" name="authors[is_corresponding][${authorIndex}]" value="1">
                <label class="form-check-label">Corr.</label>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm remove-author">✕</button>
        `;

                container.appendChild(row);
                authorIndex++;
            });

            // Delegazione eventi per il bottone di rimozione (funziona anche per gli elementi aggiunti dinamicamente)
            document.getElementById('authors-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-author') || e.target.closest('.remove-author')) {
                    const rows = document.querySelectorAll('.author-row');
                    if (rows.length > 1) {
                        e.target.closest('.author-row').remove();
                    } else {
                        alert("Devi inserire almeno un autore!");
                    }
                }
            });
        });
    </script>
@endsection

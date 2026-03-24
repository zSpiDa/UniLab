@extends('layouts.app')
@section('content')
    <div class="container pb-5">
        <h1 class="mb-4 text-dark">Modifica Pubblicazione</h1>

        <form action="{{ route('publications.update', $publication->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="title" class="form-label text-dark"><strong>Titolo</strong></label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $publication->title }}" required>
            </div>

            <div class="mb-3">
                <label for="type" class="form-label text-dark"><strong>Tipo di pubblicazione</strong></label>
                <input type="text" class="form-control" id="type" name="type" value="{{ $publication->type }}" required>
            </div>

            <div class="mb-3">
                <label for="venue" class="form-label text-dark"><strong>Luogo di pubblicazione</strong></label>
                <input type="text" class="form-control" id="venue" name="venue" value="{{ $publication->venue }}" required>
            </div>

            <div class="mb-3">
                <label for="doi" class="form-label text-dark"><strong>DOI</strong></label>
                <input type="text" class="form-control" id="doi" name="doi" value="{{ $publication->doi }}" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label text-dark"><strong>Stato</strong></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="">Seleziona uno stato</option>
                    <option value="drafting" {{ $publication->status == 'drafting' ? 'selected' : '' }}>Bozza</option>
                    <option value="submitted" {{ $publication->status == 'submitted' ? 'selected' : '' }}>Inviato</option>
                    <option value="accepted" {{ $publication->status == 'accepted' ? 'selected' : '' }}>Accettato</option>
                    <option value="published" {{ $publication->status == 'published' ? 'selected' : '' }}>Pubblicato</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="target_deadline" class="form-label text-dark"><strong>Deadline della pubblicazione</strong></label>
                <input type="date" class="form-control" id="target_deadline" name="target_deadline" value="{{ $publication->target_deadline ?? '' }}">
            </div>

            <div class="mb-3">
                <label for="projects" class="form-label text-dark"><strong>Progetto associato</strong></label>
                <div class="text-muted small mb-2">Seleziona uno o più progetti associati a questa pubblicazione: </div>
                <select class="form-select" id="projects" name="projects[]" required multiple>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ in_array($project->id, $publication->projects->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label d-block text-dark"><strong>Autori associati</strong></label>
                <div id="authors-container">
                    @foreach($publication->authors->sortBy('order') as $idx => $author)
                        <div class="author-row d-flex gap-2 mb-2 align-items-center">
                            <select name="authors[user_id][]" class="form-select border-secondary">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $author->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="authors[order][]" class="form-control border-secondary" min="1" value="{{ $author->order }}" style="width:100px">
                            <label class="d-flex align-items-center gap-1 text-nowrap text-dark">
                                <input type="checkbox" name="authors[is_corresponding][{{ $idx }}]" value="1" {{ $author->is_corresponding ? 'checked' : '' }}> Corr.
                            </label>
                            <button type="button" class="btn btn-outline-dark btn-sm remove-author">✕</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-author" class="btn btn-secondary btn-sm mt-2">+ Aggiungi autore</button>
            </div>

            <hr class="my-4 border-secondary">

            <div class="mb-4">
                <h5 class="fw-bold text-dark"><i class="fas fa-file-pdf me-2"></i> PDF Principale</h5>
                @php
                    $mainPdf = $publication->attachments->where('type', 'main_pdf')->first();
                @endphp

                @if($mainPdf)
                    <div class="mb-2 p-2 border border-secondary rounded bg-light small">
                        <span class="fw-bold text-dark">File attuale:</span>
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($mainPdf->path) }}" target="_blank" class="text-secondary text-decoration-none fw-bold">{{ basename($mainPdf->path) }}</a>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="main_pdf" class="form-label text-muted small"><strong>Carica un nuovo documento principale (sostituirà il precedente se esiste)</strong></label>
                    <input type="file" name="main_pdf" id="main_pdf" class="form-control border-secondary" accept=".pdf">
                </div>
            </div>

            <div class="mb-5">
                <h5 class="fw-bold text-dark"><i class="fas fa-paperclip me-2"></i> Materiali Aggiuntivi</h5>
                @php
                    $materials = $publication->attachments->where('type', 'material');
                @endphp

                @if($materials->count() > 0)
                    <div class="mb-2 p-2 border border-secondary rounded bg-light small">
                        <span class="fw-bold text-dark">File attuali:</span>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach($materials as $mat)
                                <li><a href="{{ \Illuminate\Support\Facades\Storage::url($mat->path) }}" target="_blank" class="text-secondary text-decoration-none">{{ basename($mat->path) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="materials" class="form-label text-muted small"><strong>Carica ulteriori materiali (verranno aggiunti a quelli esistenti)</strong></label>
                    <input type="file" name="materials[]" id="materials" class="form-control border-secondary" multiple>
                </div>
            </div>

            <button type="submit" class="btn btn-dark btn-lg fw-bold px-4">Aggiorna Pubblicazione</button>
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
                selectClone.className = 'form-select border-secondary';
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
            orderInput.className = 'form-control border-secondary';
            orderInput.min = '1';
            orderInput.value = newIndex + 1;
            orderInput.style.width = '100px';
            row.appendChild(orderInput);

            var label = document.createElement('label');
            label.className = 'd-flex align-items-center gap-1 text-nowrap text-dark';
            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'authors[is_corresponding][' + newIndex + ']';
            checkbox.value = '1';
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(' Corr.'));
            row.appendChild(label);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-dark btn-sm remove-author';
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

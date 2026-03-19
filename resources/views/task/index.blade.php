@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center py-3">
                        <span>Tutte le Tasks</span>
                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <tbody>
                                @forelse($tasks as $task)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold fs-6">{{ str_replace('Task: ', '', $task->title) }}</div>
                                            @if($task->tags && $task->tags->isNotEmpty())
                                                <div class="mt-2 d-flex flex-wrap gap-1">
                                                    @foreach($task->tags as $tag)
                                                        <span class="badge bg-light text-dark border" style="font-size: 0.70rem;">#{{ $tag->name }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if($task->due_date)
                                                <small class="text-muted">
                                                    Scadenza: {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                                </small>
                                            @endif
                                        </td>

                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary fw-bold px-3">
                                                    Dettagli
                                                </a>

                                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-warning fw-bold px-3">
                                                    Modifica
                                                </a>

                                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Sei sicuro di voler eliminare questa task?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3">
                                                        Elimina
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-5 text-muted fst-italic">
                                            Nessuna attività trovata.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>

                    @if($tasks->hasPages())
                        <div class="card-footer bg-light border-top-0 pt-3">
                            {{ $tasks->links('pagination::bootstrap-5') }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection

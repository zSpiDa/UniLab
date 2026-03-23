<?php

namespace App\Http\Controllers;

use App\Models\{Publication, Project, Author, Attachment, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException; // <-- Aggiunto per il throw error nel workflow

class PublicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:pi,manager,researcher')->only(['create','store','edit','update','destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $publications = Publication::with(['projects','authors.user','attachments'])->latest()->paginate(10);
        return view('publications.index', compact('publications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();
        $statuses = ['drafting','submitted','accepted','published'];
        return view('publications.create', compact('projects','users','statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        DB::transaction(function () use ($request, $validated) {
            $publication = Publication::create($validated);

            // Progetti collegati
            $publication->projects()->sync($request->input('projects', []));

            // Autori con ordine
            $this->syncAuthors($publication, $request);

            // Upload file PDF principale (opzionale)
            if ($request->hasFile('main_pdf')) {
                $file = $request->file('main_pdf');
                $filename = $file->getClientOriginalName(); // Recupera il nome originale

                // Salva nel disco 'public' mantenendo il nome originale
                $path = $file->storeAs('publications/' . $publication->id, $filename, 'public');

                $publication->attachments()->create([
                    'path' => $path,
                    'type' => 'main_pdf',
                    'uploaded_by' => auth()->id(),
                ]);
            }

            // Upload materiali aggiuntivi (multipli)
            if ($request->hasFile('materials')) {
                foreach ($request->file('materials') as $file) {
                    $filename = $file->getClientOriginalName(); // Recupera il nome originale

                    // Salva nel disco 'public' mantenendo il nome originale
                    $path = $file->storeAs('publications/' . $publication->id . '/materials', $filename, 'public');

                    $publication->attachments()->create([
                        'path' => $path,
                        'type' => 'material',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('publications.index')->with('success', 'Pubblicazione creata.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Publication $publication)
    {
        $publication->load(['projects','authors.user','attachments']);
        return view('publications.show', compact('publication'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Publication $publication)
    {
        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();
        $statuses = ['drafting','submitted','accepted','published'];
        $publication->load(['projects','authors.user']);
        return view('publications.edit', compact('publication','projects','users','statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Publication $publication)
    {
        // Validiamo i dati usando il metodo helper privato sotto
        $validated = $this->validateData($request);

        DB::transaction(function () use ($request, $validated, $publication) {

            $publicationData = Arr::except($validated, ['authors', 'projects', 'materials', 'main_pdf']);

            // Aggiorniamo la pubblicazione esistente
            $publication->update($publicationData);

            // Salvataggio relazioni (Progetti)
            if (!empty($validated['projects'])) {
                $publication->projects()->sync($validated['projects']);
            }

            // Salvataggio Autori (usiamo il metodo helper dedicato)
            $this->syncAuthors($publication, $request);

            //Workflow dove non bisogna tornare indietro di stato (es. da published a submitted)
            if (isset($validated['status']) && $validated['status'] !== $publication->status) {
                $allowedTransitions = [
                    'drafting' => ['submitted'],
                    'submitted' => ['accepted', 'drafting'],
                    'accepted' => ['published', 'submitted'],
                    'published' => ['accepted'],
                ];
                if (!in_array($validated['status'], $allowedTransitions[$publication->status] ?? [])) {
                    throw ValidationException::withMessages(['status' => 'Transizione di stato non consentita.']);
                }
            }

            // Upload PDF principale
            if ($request->hasFile('main_pdf')) {
                $file = $request->file('main_pdf');
                $filename = $file->getClientOriginalName();

                $path = $file->storeAs('publications/' . $publication->id, $filename, 'public');

                $publication->attachments()->create([
                    'path' => $path,
                    'type' => 'main_pdf',
                    'uploaded_by' => auth()->id(),
                ]);
            }

            // Upload Materiali Aggiuntivi
            if ($request->hasFile('materials')) {
                foreach ($request->file('materials') as $file) {
                    $filename = $file->getClientOriginalName();

                    $path = $file->storeAs('publications/' . $publication->id . '/materials', $filename, 'public');

                    $publication->attachments()->create([
                        'path' => $path,
                        'type' => 'material',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('publications.index')->with('success', 'Pubblicazione modificata con successo.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publication $publication)
    {
        $publication->delete();
        return redirect()->route('publications.index')->with('success', 'Pubblicazione eliminata.');
    }

    private function validateData(Request $request): array
    {
        $allowedStatuses = ['drafting','submitted','accepted','published'];
        return $request->validate([
            'title' => ['required','string','max:255'],
            'type' => ['nullable','string','max:100'],
            'venue' => ['nullable','string','max:255'],
            'doi' => ['nullable','string','max:255'],
            'status' => ['nullable','in:'.implode(',', $allowedStatuses)],
            'target_deadline' => ['nullable','date'],
            'projects' => ['array'],
            'projects.*' => ['integer','exists:projects,id'],
            'authors.user_id' => ['array'],
            'authors.user_id.*' => ['integer','exists:users,id'],
            'authors.order' => ['array'],
            'authors.order.*' => ['integer','min:1'],
            'authors.is_corresponding' => ['array'],
            'authors.is_corresponding.*' => ['nullable','boolean'],
            'main_pdf' => ['nullable','file','mimes:pdf','max:20480'],
            'materials' => ['nullable','array'],
            'materials.*' => ['file','max:20480'],
        ]);
    }

    private function syncAuthors(Publication $publication, Request $request): void
    {
        $userIds = $request->input('authors.user_id', []);
        $orders = $request->input('authors.order', []);
        $correspondings = $request->input('authors.is_corresponding', []);

        // Reset lista autori e ricrea secondo l'ordine fornito
        $publication->authors()->delete();

        foreach ($userIds as $idx => $uid) {
            if (!$uid) { continue; }
            $publication->authors()->create([
                'user_id' => (int)$uid,
                'order' => isset($orders[$idx]) ? (int)$orders[$idx] : ($idx + 1),
                'is_corresponding' => isset($correspondings[$idx]) && (bool)$correspondings[$idx],
            ]);
        }
    }
}

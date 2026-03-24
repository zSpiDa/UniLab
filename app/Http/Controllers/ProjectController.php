<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\Tag;
use App\Models\Milestone;
use App\Models\Publication;
use App\Models\Attachment;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['milestones', 'tags', 'publications', 'users'])
            ->orderBy('title')
            ->get();

        return view('projects.index', ['projects' => $projects]);
    }

    public function show(Project $project)
    {
        // riga corretta: aggiungo .tags dopo tasks
        $project->load(['milestones', 'publications.authors', 'tags', 'attachments', 'comments.user', 'users', 'tasks.tags']);
        $users = User::orderBy('name')->get();

        return view('projects.show', ['project' => $project, 'users' => $users]);
    }

    public function addMember(Request $request, Project $project)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role'    => ['required', 'in:pi,manager,researcher,collaborator'],
        ]);

        $project->users()->syncWithoutDetaching([
            $request->user_id => [
                'role'   => $request->role,
                'effort' => $request->effort ?? null,
            ],
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Membro aggiunto al team.');
    }

    public function removeMember(Project $project, User $user)
    {
        $project->users()->detach($user->id);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Membro rimosso dal team.');
    }

    public function removePublication(Project $project, Publication $publication)
    {
        // Controlli di sicurezza opzionali (es. se solo il manager può farlo)
        $user = auth()->user();
        if ($user->role === 'manager' && !$project->users->contains($user->id)) {
            abort(403, 'Non puoi modificare un progetto di cui non fai parte.');
        }

        // Scollega la pubblicazione dal progetto
        $project->publications()->detach($publication->id);

        return redirect()->back()->with('success', 'Pubblicazione scollegata dal progetto con successo.');
    }

    public function create()
    {
        if (auth()->user()->cannot('create', Project::class)) {
            abort(403, 'Non hai il permesso di creare un progetto.');
        }

        $users = User::orderBy('name')->get();
        return view('projects.create', compact('users'));
    }

    public function store(Request $request)
    {
        // 1. Regole base per la data di fine
        $endDateRules = ['nullable', 'date', 'after_or_equal:start_date'];

        // 2. Controllo dinamico: se è in corso, non può essere nel passato
        if ($request->status === 'ongoing') {
            $endDateRules[] = 'after_or_equal:today';
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'status'      => 'required|string|max:100',
            'code'        => 'required|string|max:255',
            'funder'      => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => $endDateRules, // Applichiamo le regole dinamiche
            'description' => 'required|string',
            'tags'         => 'nullable|string',
            'milestones'   => 'nullable|array',
            'publications' => 'nullable|string',
            'tasks'        => 'nullable|array',
            'users'        => 'nullable|array',
            'users.*'      => 'exists:users,id',
            'file'         => 'nullable|mimes:pdf|max:20480',
        ], [
            // Messaggio di errore personalizzato
            'end_date.after_or_equal' => 'La data di fine deve essere successiva all\'inizio. Se il progetto è "In Corso", non può essere una data passata.'
        ]);

        $tagsInput = $validated['tags'] ?? null;
        $usersInput = $validated['users'] ?? [];
        $tasksInput = $validated['tasks'] ?? []; // <-- PRENDIAMO I DATI DELLA TASK

        // Rimuoviamo i campi che non appartengono direttamente alla tabella projects
        unset($validated['tags'], $validated['file'], $validated['milestones'], $validated['publications'], $validated['tasks'], $validated['users']);

        DB::transaction(function () use ($validated, $request, $tagsInput, $usersInput, $tasksInput) {

            $project = Project::create($validated);

            // --- GESTIONE MEMBRI ---
            $syncData = [];
            foreach ($usersInput as $userId) {
                $userRole = User::find($userId)->role ?? 'collaborator';
                $syncData[$userId] = ['role' => $userRole];
            }
            $project->users()->sync($syncData);

            // --- GESTIONE FILE ---
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('projects', 'public');
                $project->attachments()->create([
                    'path' => $path,
                    'name' => $request->file('file')->getClientOriginalName(),
                    'uploaded_by' => auth()->id(),
                ]);
            }

            // --- GESTIONE TAG ---
            if ($tagsInput) {
                $tagNames = array_map('trim', explode(',', $tagsInput));
                $tagIds = [];
                foreach ($tagNames as $name) {
                    if(!empty($name)){
                        $tagIds[] = Tag::firstOrCreate(['name' => $name])->id;
                    }
                }
                $project->tags()->sync($tagIds);
            }

            // --- GESTIONE MILESTONE ---
            $createdMilestones = []; // Creiamo una mappa per ricordarci gli ID appena creati
            if ($request->filled('milestones')) {
                foreach ($request->milestones as $index => $m) {
                    if(is_array($m)) {
                        $newMilestone = $project->milestones()->create([
                            'title'    => $m['title'] ?? 'Milestone',
                            'due_date' => $m['due_date'] ?? null,
                            'status'   => $m['status'] ?? 'active',
                        ]);
                        // Salviamo l'ID corrispondente all'indice Javascript
                        $createdMilestones[$index] = $newMilestone->id;
                    }
                }
            }

            // --- GESTIONE TASK INIZIALE CON TAGS ---
            if (!empty($tasksInput)) {
                foreach ($tasksInput as $taskData) {
                    if (!empty($taskData['title'])) {

                        // Controlliamo se la task va associata a una milestone appena creata
                        $milestoneId = null;
                        if (isset($taskData['milestone_index']) && $taskData['milestone_index'] !== '') {
                            $mIndex = $taskData['milestone_index'];
                            if (isset($createdMilestones[$mIndex])) {
                                $milestoneId = $createdMilestones[$mIndex]; // Peschiamo l'ID vero!
                            }
                        }

                        $newTask = $project->tasks()->create([
                            'title'        => $taskData['title'],
                            'description'  => $taskData['description'] ?? null,
                            'status'       => $taskData['status'] ?? 'open',
                            'priority'     => $taskData['priority'] ?? 'medium',
                            'due_date'     => !empty($taskData['due_date']) ? $taskData['due_date'] : null,
                            'assignee_id'  => !empty($taskData['assignee_id']) ? $taskData['assignee_id'] : null,
                            'milestone_id' => $milestoneId, // Associamo la milestone corretta
                        ]);

                        // Se la task appena creata ha dei tags, li salviamo
                        if (!empty($taskData['tags'])) {
                            $tagNames = array_filter(array_map('trim', explode(',', $taskData['tags'])));
                            $tagIds = [];
                            foreach ($tagNames as $name) {
                                if(!empty($name)){
                                    $tagIds[] = Tag::firstOrCreate(['name' => $name])->id;
                                }
                            }
                            $newTask->tags()->sync($tagIds);
                        }
                    }
                }
            }

            // --- GESTIONE PUBBLICAZIONI ---
            if ($request->filled('publications')) {
                $items = array_map('trim', explode(',', $request->publications));
                foreach ($items as $item) {
                    $parts = array_map('trim', explode('|', $item));
                    if (count($parts) >= 1 && !empty($parts[0])) {
                        $publication = Publication::create([
                            'title'  => $parts[0],
                            'status' => $parts[1] ?? 'published',
                            'author' => $parts[2] ?? null,
                        ]);
                        $project->publications()->attach($publication->id);
                    }
                }
            }
        });

        return redirect()->route('projects.index')->with('success', 'Progetto creato con successo!');
    }

    public function edit(Project $project)
    {
        $user = auth()->user();
        if ($user->role === 'manager' && !$project->users->contains($user->id)) {
            abort(403, 'Non puoi modificare un progetto di cui non fai parte.');
        }

        $users = User::orderBy('name')->get();
        return view('projects.edit', compact('project', 'users'));
    }

    public function update(Request $request, Project $project)
    {
        $user = auth()->user();
        if ($user->role === 'manager' && !$project->users->contains($user->id)) {
            abort(403, 'Non puoi modificare un progetto di cui non fai parte.');
        }

        // 1. Regole base per la data di fine
        $endDateRules = ['nullable', 'date', 'after_or_equal:start_date'];

        // 2. Controllo dinamico
        if ($request->status === 'ongoing') {
            $endDateRules[] = 'after_or_equal:today';
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'status'      => 'required|string|max:100',
            'code'        => 'nullable|string|max:255',
            'funder'      => 'nullable|string|max:255',
            'start_date'  => 'nullable|date',
            'end_date'    => $endDateRules,
            'description' => 'nullable|string',
            'tags'        => 'nullable|string',
            'milestones'  => 'nullable|array',
            'file'        => 'nullable|mimes:pdf|max:20480',
            'tasks'       => 'nullable|array',
            'users'       => 'nullable|array',
            'users.*'     => 'exists:users,id',
        ], [
            'end_date.after_or_equal' => 'La data di fine deve essere successiva all\'inizio. Se il progetto è "In Corso", non può essere una data passata.'
        ]);

        $tagsInput = $validated['tags'] ?? null;
        $usersInput = $validated['users'] ?? [];

        unset($validated['tags'], $validated['users'], $validated['milestones'], $validated['tasks'], $validated['file']);

        DB::transaction(function () use ($validated, $request, $project, $tagsInput, $usersInput) {

            $project->update($validated);

            // --- AGGIORNAMENTO MEMBRI ---
            $syncData = [];
            if ($request->has('users')) {
                foreach ($usersInput as $userId) {
                    $userRole = User::find($userId)->role ?? 'collaborator';
                    $syncData[$userId] = ['role' => $userRole];
                }
            }
            $project->users()->sync($syncData);

            // --- AGGIORNAMENTO TAG ---
            if ($tagsInput !== null) {
                $tagNames = array_map('trim', explode(',', $tagsInput));
                $tagIds = [];
                foreach ($tagNames as $name) {
                    if(!empty($name)) {
                        $tagIds[] = Tag::firstOrCreate(['name' => $name])->id;
                    }
                }
                $project->tags()->sync($tagIds);
            }

            // --- AGGIORNAMENTO MILESTONE ---
            $sentIds = collect($request->input('milestones', []))->pluck('id')->filter()->toArray();
            $project->milestones()->whereNotIn('id', $sentIds)->delete();

            if ($request->has('milestones')) {
                foreach ($request->milestones as $m) {
                    if (isset($m['id']) && $m['id']) {
                        $milestone = $project->milestones()->find($m['id']);
                        if ($milestone) {
                            $milestone->update([
                                'title'    => $m['title'],
                                'due_date' => $m['due_date'],
                                'status'   => $m['status'],
                            ]);
                        }
                    } else {
                        $project->milestones()->create([
                            'title'    => $m['title'],
                            'due_date' => $m['due_date'],
                            'status'   => $m['status'] ?? 'active',
                        ]);
                    }
                }
            }

            // --- GESTIONE FILE ---
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('projects', 'public');
                $project->attachments()->create([
                    'path' => $path,
                    'name' => $request->file('file')->getClientOriginalName(),
                    'uploaded_by' => auth()->id(),
                ]);
            } else {
                if ($request->filled('delete_attachments')) {
                    $idsToDelete = $request->input('delete_attachments');
                    $attachmentsToDelete = $project->attachments()->whereIn('id', $idsToDelete)->get();

                    foreach ($attachmentsToDelete as $attachment) {
                        if (Storage::disk('public')->exists($attachment->path)) {
                            Storage::disk('public')->delete($attachment->path);
                        }
                        $attachment->delete();
                    }
                }
            }
        });

        return redirect()->route('projects.show', $project)->with('success', 'Progetto aggiornato correttamente.');
    }

    public function destroy(Project $project)
    {
        $user = auth()->user();
        if ($user->role === 'manager' && !$project->users->contains($user->id)) {
            abort(403, 'Non puoi eliminare un progetto di cui non fai parte.');
        }

        foreach($project->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->path);
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Progetto eliminato.');
    }

    public function html()
    {
        $projects = Project::orderBy('title')->get(['title','status']);
        $out = '<h2>Progetti</h2><ul>';
        foreach($projects as $p){
            $out .= '<li><strong>'.e($p->title).'</strong> ('.e($p->status ?? 'n/d').')</li>';
        }
        $out .= '</ul>';
        return $out;
    }

    public function exportCsv(Project $project)
    {
        $project->load(['users', 'publications']);

        return response()->streamDownload(function() use ($project) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['project_id', 'title', 'status', 'users', 'publications']);
            fputcsv($out, [
                $project->id,
                $project->title,
                $project->status,
                $project->users->pluck('name')->implode('|'),
                $project->publications->pluck('title')->implode('|'),
            ]);
            fclose($out);
        }, 'project_'.$project->id.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=project_'.$project->id.'.csv',
        ]);
    }
}

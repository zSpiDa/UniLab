<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Milestone;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        // Nota: la relazione nel modello si chiama 'user', quindi usiamo 'user' qui nel with
        $tasks = Task::with(['project', 'user'])->latest()->paginate(10);
        return view('task.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        // IMPORTANTE: Carichiamo i progetti INSIEME alle milestone
        $projects = Project::with('milestones')->orderBy('title')->get();
        return view('task.create', compact('users', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'status'      => 'required|in:open,in_progress,done',
            'priority'    => 'required|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            'target'      => 'nullable|string',
        ]);

        // Smistiamo il valore di "target" in project_id e milestone_id
        if ($request->filled('target')) {
            if (str_starts_with($request->target, 'milestone_')) {
                $milestoneId = str_replace('milestone_', '', $request->target);
                $milestone = Milestone::find($milestoneId);

                $validated['project_id'] = $milestone->project_id;
                $validated['milestone_id'] = $milestone->id;

            } elseif (str_starts_with($request->target, 'project_')) {
                $validated['project_id'] = str_replace('project_', '', $request->target);
                $validated['milestone_id'] = null;
            }
        } else {
            $validated['project_id'] = null;
            $validated['milestone_id'] = null;
        }

        // Rimuoviamo 'target' perché non è una colonna del DB
        unset($validated['target']);

        // Crea la Task
        Task::create($validated);

        // Redirect intelligente: torniamo sempre alla pagina da cui abbiamo compilato il form!
        return redirect()->back()
            ->with('success', 'Task creata con successo!');
    }

    public function show(Task $task)
    {
        $task->load(['user', 'project', 'milestone']); // Carichiamo anche milestone se serve nella view
        return view('task.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $users = User::orderBy('name')->get();
        // IMPORTANTE: Carichiamo i progetti INSIEME alle milestone
        $projects = Project::with('milestones')->orderBy('title')->get();
        return view('task.edit', compact('task', 'users', 'projects'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'status'      => 'required|in:open,in_progress,done',
            'priority'    => 'required|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            // Valideremo il nuovo campo 'target' che ci arriva dal form
            'target'      => 'nullable|string',
        ]);

        // Smistiamo il valore di "target" in project_id e milestone_id
        if ($request->filled('target')) {
            if (str_starts_with($request->target, 'milestone_')) {
                $milestoneId = str_replace('milestone_', '', $request->target);
                $milestone = Milestone::find($milestoneId);

                $validated['project_id'] = $milestone->project_id;
                $validated['milestone_id'] = $milestone->id;
            } elseif (str_starts_with($request->target, 'project_')) {
                $validated['project_id'] = str_replace('project_', '', $request->target);
                $validated['milestone_id'] = null;
            }
        } else {
            $validated['project_id'] = null;
            $validated['milestone_id'] = null;
        }

        // Rimuoviamo 'target' dall'array
        unset($validated['target']);

        $task->update($validated);

        // ECCO LA MODIFICA: Ora torniamo indietro invece di andare a tasks.index
        return redirect()->back()
            ->with('success', 'Task aggiornata con successo!');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Task eliminata.');
    }
}

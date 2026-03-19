<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\Tag; // <-- AGGIUNTO IL MODELLO TAG
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with(['project', 'user'])->latest()->paginate(10);
        return view('task.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
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
            'assignee_id' => 'required|exists:users,id',
            'target'      => 'nullable|string',
            'tags'        => 'nullable|string', // <-- AGGIUNTO ALLA VALIDAZIONE
        ]);

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

        unset($validated['target']);

        // Creiamo la task e la assegniamo a una variabile
        $task = Task::create($validated);

        // --- SALVATAGGIO TAG DELLA NUOVA TASK ---
        if (!empty($request->tags)) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds = [];
            foreach ($tagNames as $name) {
                if(!empty($name)) {
                    $tagIds[] = Tag::firstOrCreate(['name' => $name])->id;
                }
            }
            $task->tags()->sync($tagIds);
        }

        return redirect()->back()
            ->with('success', 'Task creata con successo!');
    }

    public function show(Task $task)
    {
        $task->load(['user', 'project', 'milestone', 'tags']); // <-- Aggiunto 'tags'
        return view('task.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $users = User::orderBy('name')->get();
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
            // Messo 'nullable' altrimenti il form di modifica veloce dal progetto andava in blocco!
            'assignee_id' => 'nullable|exists:users,id',
            'target'      => 'nullable|string',
            'tags'        => 'nullable|string', // <-- AGGIUNTO ALLA VALIDAZIONE
        ]);

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

        unset($validated['target']);

        $task->update($validated);

        // --- AGGIORNAMENTO TAG DELLA TASK ---
        if ($request->has('tags')) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->tags)));
            $tagIds = [];
            foreach ($tagNames as $name) {
                if(!empty($name)) {
                    $tagIds[] = Tag::firstOrCreate(['name' => $name])->id;
                }
            }
            $task->tags()->sync($tagIds);
        }

        // Modificato in redirect()->back() così resti comodamente nella pagina in cui stavi lavorando!
        return redirect()->back()
            ->with('success', 'Task aggiornata con successo!');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Task eliminata.');
    }
}

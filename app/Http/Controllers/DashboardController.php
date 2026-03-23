<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Publication; // <-- Aggiunto per le pubblicazioni
use Carbon\Carbon;          // <-- Aggiunto per calcolare i 7 giorni

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. CARICAMENTO PROGETTI
        $projects = $user->projects()->get();

        // 2. LE MIE PUBBLICAZIONI (Indipendenti dai progetti!)
        // Peschiamo direttamente tutte le pubblicazioni in cui l'utente loggato è autore
        $myPublications = Publication::with('authors.user')
            ->whereHas('authors', function ($query) use ($user) {
                // Controllo se l'utente è tra gli autori (relazione DB)
                $query->where('user_id', $user->id);
            })
            // Oppure controllo se il suo nome è stato scritto a mano nel campo di testo
            ->orWhere('author', 'LIKE', '%' . $user->name . '%')
            ->get();

        // 3. CALCOLO KPI (I contatori per le card)
        $assignedTasksCount = Task::where('assignee_id', $user->id)->count();

        $scheduledTasksCount = Task::where('assignee_id', $user->id)
            ->where('status', '!=', 'done')
            ->count();

        // 4. LISTA TASK PER LA TABELLA
        $myTasks = Task::where('assignee_id', $user->id)
            ->with('project')
            ->orderBy('due_date', 'asc')
            ->take(10)
            ->get();

        // 5. LISTA MILESTONE (Solo dei progetti di cui fai parte)
        $projectIds = $projects->pluck('id');

        $milestones = Milestone::whereIn('project_id', $projectIds)
            ->with('project')
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // ---------------------------------------------------------
        // 6. NOTIFICHE E PROMEMORIA
        // ---------------------------------------------------------
        $traUnaSettimana = Carbon::today()->addDays(7);

        $upcomingTasks = Task::where('assignee_id', $user->id)
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $traUnaSettimana)
            ->with('project')
            ->orderBy('due_date', 'asc')
            ->get();

        // Notifiche per TUTTE le TUE pubblicazioni (anche senza progetto)
        $upcomingPublications = Publication::whereIn('id', $myPublications->pluck('id'))
            ->whereNotIn('status', ['published', 'accepted'])
            ->whereNotNull('target_deadline')
            ->where('target_deadline', '<=', $traUnaSettimana)
            ->orderBy('target_deadline', 'asc')
            ->get();

        $upcomingMilestones = Milestone::whereIn('project_id', $projectIds)
            ->whereNotIn('status', ['completed', 'done'])
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $traUnaSettimana)
            ->with('project')
            ->orderBy('due_date', 'asc')
            ->get();

        $totaleNotifiche = $upcomingTasks->count() + $upcomingPublications->count() + $upcomingMilestones->count();

        return view('dashboard', compact(
            'user',
            'projects',
            'myPublications',
            'myTasks',
            'assignedTasksCount',
            'scheduledTasksCount',
            'milestones',
            'upcomingTasks',
            'upcomingPublications',
            'upcomingMilestones',
            'totaleNotifiche'
        ));
    }
}

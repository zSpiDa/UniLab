<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Publication;
use App\Notifications\DeadlineReminder;
use Carbon\Carbon;

class SendDeadlineReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $traUnaSettimana = Carbon::today()->addDays(7);

        // 1. Conta le Task dell'utente in scadenza
        $tasksCount = Task::where('assignee_id', $this->user->id)
            ->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $traUnaSettimana)
            ->count();

        // Ottieni gli ID dei progetti dell'utente
        $projectIds = $this->user->projects()->pluck('projects.id');

        // 2. Conta le Milestone in scadenza per quei progetti
        $milestonesCount = Milestone::whereIn('project_id', $projectIds)
            ->whereNotIn('status', ['completed', 'done'])
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $traUnaSettimana)
            ->count();

        // 3. Conta le Pubblicazioni in scadenza per quei progetti
        $pubsCount = Publication::whereHas('projects', function($q) use ($projectIds) {
            $q->whereIn('projects.id', $projectIds);
        })
            ->whereNotIn('status', ['published', 'accepted'])
            ->whereNotNull('target_deadline')
            ->where('target_deadline', '<=', $traUnaSettimana)
            ->count();

        $totale = $tasksCount + $milestonesCount + $pubsCount;

        // Se c'è almeno 1 scadenza, invia l'email
        if ($totale > 0) {
            $this->user->notify(new DeadlineReminder($totale));
        }

        sleep(1); // Piccola pausa per evitare sovraccarico
    }
}

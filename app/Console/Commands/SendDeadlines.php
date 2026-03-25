<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Jobs\SendDeadlineReminderJob;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Publication;
use Carbon\Carbon;
use App\Services\NotificationService;

class SendDeadlines extends Command
{
    // Il nome del comando da lanciare nel terminale
    protected $signature = 'app:send-deadlines';
    protected $description = 'Invia e-mail giornaliere per le scadenze imminenti';
    

    public function handle()
    {
        $users = User::all();
        $dispatched = 0;
        $createdInApp = 0;

        $targetDate = Carbon::today()->addDays(7)->toDateString();

        foreach ($users as $user) {
            // Dispatcha il job nella queue
            \App\Jobs\SendDeadlineReminderJob::dispatch($user);
            $dispatched++;

            $tasks = Task::query()
                ->where('assignee_id', $user->id)
                ->where('status', '!=', 'done')
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($tasks as $task) {
                NotificationService::notifyTaskDeadlineReminder($task);
                $createdInApp++;
            }

            $projectIds = $user->projects()->pluck('projects.id');
            $milestones = Milestone::query()
                ->whereIn('project_id', $projectIds)
                ->whereNotIn('status', ['completed', 'done'])
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($milestones as $milestone) {
                $milestone->loadMissing('project');
                NotificationService::notifyMilestoneDeadlineReminder($milestone, $user);
                $createdInApp++;
            }

            $publications = Publication::query()
                ->whereHas('projects.users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->whereNotIn('status', ['accepted', 'published'])
                ->whereDate('target_deadline', $targetDate)
                ->get();

            foreach ($publications as $publication) {
                NotificationService::notifyPublicationDeadlineReminder($publication, $user);
                $createdInApp++;
            }
        }

        $this->info("✅ Accodati {$dispatched} job nella queue.");
        $this->info("🔔 Generate/aggiornate notifiche in-app reminder: {$createdInApp}.");
        $this->info("⚠️  Avvia il worker con: php artisan queue:work");
    }
}

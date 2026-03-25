<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Notification;
use App\Models\User;
use App\Models\Task;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Publication;

class NotificationService
{
    private static function createUnique(array $data): void
    {
        $exists = Notification::query()
            ->where('user_id', $data['user_id'])
            ->where('type', $data['type'])
            ->where('notifiable_type', $data['notifiable_type'] ?? null)
            ->where('notifiable_id', $data['notifiable_id'] ?? null)
            ->where('message', $data['message'])
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if (! $exists) {
            Notification::create($data);
        }
    }

    /**
     * Crea una notifica per task assegnata
     */
    public static function notifyTaskAssigned(Task $task, User $assignee): void
    {
        self::createUnique([
            'user_id' => $assignee->id,
            'type' => 'task_assigned',
            'title' => '📋 Nuova Task Assegnata',
            'message' => "Ti è stata assegnata la task \"{$task->title}\" nel progetto \"{$task->project->title}\".",
            'notifiable_type' => Task::class,
            'notifiable_id' => $task->id,
        ]);
    }

    /**
     * Crea notifica per task a 7 giorni dalla deadline.
     */
    public static function notifyTaskDeadlineReminder(Task $task): void
    {
        if (! $task->assignee_id || ! $task->due_date || $task->status === 'done') {
            return;
        }

        $daysUntil = Carbon::parse($task->due_date)->startOfDay()->diffInDays(Carbon::today(), false);
        if ($daysUntil !== 7) {
            return;
        }

        self::createUnique([
            'user_id' => $task->assignee_id,
            'type' => 'task_deadline_reminder',
            'title' => '⏰ Deadline Task tra 7 giorni',
            'message' => "La task \"{$task->title}\" scade il " . Carbon::parse($task->due_date)->format('d/m/Y') . '.',
            'notifiable_type' => Task::class,
            'notifiable_id' => $task->id,
        ]);
    }

    /**
     * Crea notifica per milestone creata.
     */
    public static function notifyMilestoneCreated(Milestone $milestone): void
    {
        $projectMembers = $milestone->project->users;

        foreach ($projectMembers as $member) {
            self::createUnique([
                'user_id' => $member->id,
                'type' => 'milestone_created',
                'title' => '🎯 Nuova Milestone',
                'message' => "È stata creata una nuova milestone \"{$milestone->title}\" nel progetto \"{$milestone->project->title}\".",
                'notifiable_type' => Milestone::class,
                'notifiable_id' => $milestone->id,
            ]);
        }
    }

    /**
     * Crea notifica per milestone a 7 giorni dalla deadline.
     */
    public static function notifyMilestoneDeadlineReminder(Milestone $milestone, User $user): void
    {
        if (! $milestone->due_date || in_array($milestone->status, ['completed', 'done'], true)) {
            return;
        }

        $daysUntil = Carbon::parse($milestone->due_date)->startOfDay()->diffInDays(Carbon::today(), false);
        if ($daysUntil !== 7) {
            return;
        }

        self::createUnique([
            'user_id' => $user->id,
            'type' => 'milestone_deadline_reminder',
            'title' => '⏰ Deadline Milestone tra 7 giorni',
            'message' => "La milestone \"{$milestone->title}\" del progetto \"{$milestone->project->title}\" scade il " . Carbon::parse($milestone->due_date)->format('d/m/Y') . '.',
            'notifiable_type' => Milestone::class,
            'notifiable_id' => $milestone->id,
        ]);
    }

    /**
     * Notifica aggiunta a progetto
     */
    public static function notifyUserAddedToProject(User $user, Project $project): void
    {
        self::createUnique([
            'user_id' => $user->id,
            'type' => 'project_added_member',
            'title' => '👥 Aggiunto a Progetto',
            'message' => "Sei stato aggiunto al progetto \"{$project->title}\".",
            'notifiable_type' => Project::class,
            'notifiable_id' => $project->id,
        ]);
    }

    public static function notifyTaskDeadlineChanged(Task $task, ?string $oldDueDate): void
    {
        if (! $task->assignee_id || ! $task->due_date) {
            return;
        }

        $old = $oldDueDate ? Carbon::parse($oldDueDate)->format('d/m/Y') : 'non impostata';
        $new = Carbon::parse($task->due_date)->format('d/m/Y');

        self::createUnique([
            'user_id' => $task->assignee_id,
            'type' => 'task_deadline_changed',
            'title' => '📅 Deadline Task aggiornata',
            'message' => "La deadline della task \"{$task->title}\" è cambiata da {$old} a {$new}.",
            'notifiable_type' => Task::class,
            'notifiable_id' => $task->id,
        ]);
    }

    public static function notifyMilestoneDeadlineChanged(Milestone $milestone, ?string $oldDueDate): void
    {
        if (! $milestone->due_date) {
            return;
        }

        $old = $oldDueDate ? Carbon::parse($oldDueDate)->format('d/m/Y') : 'non impostata';
        $new = Carbon::parse($milestone->due_date)->format('d/m/Y');

        $members = $milestone->project->users;
        foreach ($members as $member) {
            self::createUnique([
                'user_id' => $member->id,
                'type' => 'milestone_deadline_changed',
                'title' => '📅 Deadline Milestone aggiornata',
                'message' => "La milestone \"{$milestone->title}\" ha cambiato deadline da {$old} a {$new}.",
                'notifiable_type' => Milestone::class,
                'notifiable_id' => $milestone->id,
            ]);
        }
    }

    public static function notifyTaskStatusChanged(Task $task, ?string $oldStatus): void
    {
        if (! $oldStatus || $oldStatus === $task->status) {
            return;
        }

        $recipients = collect();
        if ($task->assignee_id) {
            $recipients->push($task->assignee_id);
        }

        if ($task->project) {
            $managerIds = $task->project->users
                ->filter(fn ($u) => in_array($u->pivot->role, ['pi', 'manager'], true))
                ->pluck('id')
                ->all();
            $recipients = $recipients->merge($managerIds);
        }

        foreach ($recipients->unique() as $userId) {
            self::createUnique([
                'user_id' => $userId,
                'type' => 'task_status_changed',
                'title' => '🔄 Stato Task aggiornato',
                'message' => "La task \"{$task->title}\" è passata da {$oldStatus} a {$task->status}.",
                'notifiable_type' => Task::class,
                'notifiable_id' => $task->id,
            ]);
        }
    }

    public static function notifyPublicationStatusChanged(Publication $publication, ?string $oldStatus): void
    {
        if (! $oldStatus || $oldStatus === $publication->status) {
            return;
        }

        $recipientIds = collect();
        $recipientIds = $recipientIds->merge($publication->authors()->pluck('user_id')->all());
        foreach ($publication->projects as $project) {
            $recipientIds = $recipientIds->merge(
                $project->users
                    ->filter(fn ($u) => in_array($u->pivot->role, ['pi', 'manager'], true))
                    ->pluck('id')
                    ->all()
            );
        }

        foreach ($recipientIds->unique() as $userId) {
            self::createUnique([
                'user_id' => $userId,
                'type' => 'publication_status_changed',
                'title' => '📝 Stato Pubblicazione aggiornato',
                'message' => "La pubblicazione \"{$publication->title}\" è passata da {$oldStatus} a {$publication->status}.",
                'notifiable_type' => Publication::class,
                'notifiable_id' => $publication->id,
            ]);
        }
    }

    public static function notifyPublicationDeadlineChanged(Publication $publication, ?string $oldDeadline): void
    {
        if (! $publication->target_deadline) {
            return;
        }

        $old = $oldDeadline ? Carbon::parse($oldDeadline)->format('d/m/Y') : 'non impostata';
        $new = Carbon::parse($publication->target_deadline)->format('d/m/Y');

        $recipientIds = collect($publication->authors()->pluck('user_id')->all());
        foreach ($publication->projects as $project) {
            $recipientIds = $recipientIds->merge($project->users()->pluck('users.id')->all());
        }

        foreach ($recipientIds->unique() as $userId) {
            self::createUnique([
                'user_id' => $userId,
                'type' => 'publication_deadline_changed',
                'title' => '📅 Deadline Pubblicazione aggiornata',
                'message' => "La pubblicazione \"{$publication->title}\" ha cambiato deadline da {$old} a {$new}.",
                'notifiable_type' => Publication::class,
                'notifiable_id' => $publication->id,
            ]);
        }
    }

    public static function notifyPublicationDeadlineReminder(Publication $publication, User $user): void
    {
        if (! $publication->target_deadline || in_array($publication->status, ['accepted', 'published'], true)) {
            return;
        }

        $daysUntil = Carbon::parse($publication->target_deadline)->startOfDay()->diffInDays(Carbon::today(), false);
        if ($daysUntil !== 7) {
            return;
        }

        self::createUnique([
            'user_id' => $user->id,
            'type' => 'publication_deadline_reminder',
            'title' => '⏰ Deadline Pubblicazione tra 7 giorni',
            'message' => "La pubblicazione \"{$publication->title}\" scade il " . Carbon::parse($publication->target_deadline)->format('d/m/Y') . '.',
            'notifiable_type' => Publication::class,
            'notifiable_id' => $publication->id,
        ]);
    }
}
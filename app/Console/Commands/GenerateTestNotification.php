<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Notification;

class GenerateTestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {user_id?}';
    protected $description = 'Genera notifiche di test per un utente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if (!$userId) {
            // Se user_id non è specificato, mostra una lista di utenti interattiva
            $users = User::select('id', 'name', 'email')->get();
            
            if ($users->isEmpty()) {
                $this->error('❌ Nessun utente trovato nel database!');
                return 1;
            }

            $this->info("\n📋 Seleziona un utente:\n");
            
            foreach ($users as $index => $u) {
                $this->line("  [{$u->id}] {$u->name} ({$u->email})");
            }

            $userId = $this->ask('\n👤 Inserisci l\'ID utente');
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Utente con ID {$userId} non trovato!");
            return 1;
        }

        $this->info("\n✅ Creando notifiche per: {$user->name}\n");

        $notifications = [
            [
                'type' => 'task_assigned',
                'title' => '📋 Nuova Task Assegnata',
                'message' => 'Ti è stata assegnata la task "Implementare notifiche" nel progetto "UniLab".',
            ],
            [
                'type' => 'deadline_reminder',
                'title' => '⏰ Deadline Imminente',
                'message' => 'La task "Revisione API" scade tra 2 giorni.',
            ],
            [
                'type' => 'milestone_created',
                'title' => '🎯 Nuova Milestone',
                'message' => 'È stata creata una nuova milestone "Alpha Release" nel progetto "UniLab".',
            ],
            [
                'type' => 'project_added_member',
                'title' => '👥 Aggiunto a Progetto',
                'message' => 'Sei stato aggiunto al progetto "Research Lab".',
            ],
            [
                'type' => 'deadline_exceeded',
                'title' => '⛔ Deadline Superata',
                'message' => 'La task "Deploy Production" ha superato la deadline!',
            ],
        ];

        foreach ($notifications as $notif) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $notif['type'],
                'title' => $notif['title'],
                'message' => $notif['message'],
                'notifiable_type' => null,
                'notifiable_id' => null,
                'is_read' => false,
            ]);
            $this->line("  ✓ {$notif['title']}");
        }

        $this->info("\n🎉 Fatto! {$user->name} ha ricevuto " . count($notifications) . " notifiche di test!");
        $this->line("🔔 Accedi come {$user->email} e visualizza le notifiche sulla dashboard\n");
    }
}
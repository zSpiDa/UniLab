<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Jobs\SendDeadlineReminderJob;

class SendDeadlines extends Command
{
    // Il nome del comando da lanciare nel terminale
    protected $signature = 'app:send-deadlines';
    protected $description = 'Invia e-mail giornaliere per le scadenze imminenti';
    

    public function handle()
    {
        $users = User::all();
        $dispatched = 0;

        foreach ($users as $user) {
            // Dispatcha il job nella queue
            \App\Jobs\SendDeadlineReminderJob::dispatch($user);
            $dispatched++;
        }

        $this->info("✅ Accodati {$dispatched} job nella queue.");
        $this->info("⚠️  Avvia il worker con: php artisan queue:work");
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

#[Signature('notifications:clear-old-read')]
#[Description('Clearing old and already read notifications.')]
class ClearOldNotifications extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        User::query()
            ->where('notifications_ttl_days', '>', 0)
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $user->notifications()
                        ->whereNotNull('read_at')
                        ->where('read_at', '<', Carbon::now()->subDays($user->notifications_ttl_days))
                        ->delete();
                }
            });

        //$this->info('Old and already read notifications have been deleted!');

        return CommandAlias::SUCCESS;

    }
}

<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        // Queueable notification dispatch placeholder
    }
}

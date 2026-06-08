<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AuditLogListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        // Queueable audit logging implementation placeholder
    }
}

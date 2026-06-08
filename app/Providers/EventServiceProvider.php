<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public array $listen = [
        \App\Events\QuestionCreated::class => [
            \App\Listeners\AuditLogListener::class,
            \App\Listeners\NotificationListener::class,
        ],
        \App\Events\AttemptSubmitted::class => [
            \App\Listeners\AuditLogListener::class,
            \App\Listeners\NotificationListener::class,
        ],
        \App\Events\AssignmentCreated::class => [
            \App\Listeners\AuditLogListener::class,
            \App\Listeners\NotificationListener::class,
        ],
    ];

    public function register(): void
    {
        // noop
    }

    public function boot(): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                \Illuminate\Support\Facades\Event::listen($event, $listener);
            }
        }
    }
}

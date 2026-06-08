<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations here
        // Example bindings are added below for pattern illustration.
        $this->app->bind(
            \App\Modules\User\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Modules\User\Repositories\UserRepository::class
        );

        $this->app->bind(
            \App\Modules\Question\Repositories\Contracts\QuestionRepositoryInterface::class,
            \App\Modules\Question\Repositories\QuestionRepository::class
        );

        $this->app->bind(
            \App\Modules\Tenant\Repositories\Contracts\TenantRepositoryInterface::class,
            \App\Modules\Tenant\Repositories\TenantRepository::class
        );

        $this->app->bind(
            \App\Modules\Role\Repositories\Contracts\RoleRepositoryInterface::class,
            \App\Modules\Role\Repositories\RoleRepository::class
        );

        $this->app->bind(
            \App\Modules\Permission\Repositories\Contracts\PermissionRepositoryInterface::class,
            \App\Modules\Permission\Repositories\PermissionRepository::class
        );

        $this->app->bind(
            \App\Modules\Test\Repositories\Contracts\TestRepositoryInterface::class,
            \App\Modules\Test\Repositories\TestRepository::class
        );

        $this->app->bind(
            \App\Modules\Assignment\Repositories\Contracts\AssignmentRepositoryInterface::class,
            \App\Modules\Assignment\Repositories\AssignmentRepository::class
        );

        $this->app->bind(
            \App\Modules\Attempt\Repositories\Contracts\AttemptRepositoryInterface::class,
            \App\Modules\Attempt\Repositories\AttemptRepository::class
        );

        $this->app->bind(
            \App\Modules\Answer\Repositories\Contracts\AnswerRepositoryInterface::class,
            \App\Modules\Answer\Repositories\AnswerRepository::class
        );

        $this->app->bind(
            \App\Modules\Audit\Repositories\Contracts\AuditRepositoryInterface::class,
            \App\Modules\Audit\Repositories\AuditRepository::class
        );
    }

    public function boot(): void
    {
    }
}

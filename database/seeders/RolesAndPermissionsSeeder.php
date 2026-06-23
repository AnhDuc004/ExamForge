<?php

namespace Database\Seeders;

use App\Modules\Permission\Models\Permission;
use App\Modules\Role\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions: [resource => [action, ...]]
     */
    private array $permissions = [
        'users'       => ['view', 'create', 'update-status'],
        'roles'       => ['view'],
        'media'       => ['presigned-upload', 'create'],
        'questions'   => ['view', 'create', 'bulk-import', 'bulk-update'],
        'tests'       => ['view', 'create', 'update-sections', 'publish'],
        'assignments' => ['create', 'view', 'verify'],
        'attempts'    => ['start', 'view', 'heartbeat', 'save-answers', 'resume', 'submit', 'force-submit'],
        'grading'     => ['view-pending', 'review-answer', 'finalize'],
        'reports'     => ['view', 'export'],
        'jobs'        => ['download'],
        'ai'          => ['generate-questions', 'suggest-feedback'],
        'system'      => ['health'],
        'tenant'      => ['settings', 'manage'],
        'audit'       => ['view'],
    ];

    /**
     * Roles: name => [[resource, action], ...]
     */
    private array $roles = [
        'System Admin' => [
            ['system', 'health'],
            ['tenant', 'settings'],
            ['tenant', 'manage'],
        ],
        'Admin' => [
            ['users',    'view'],
            ['users',    'create'],
            ['users',    'update-status'],
            ['roles',    'view'],
            ['tenant',   'settings'],
            ['tenant',   'manage'],
            ['audit',    'view'],
            ['reports',  'view'],
            ['reports',  'export'],
            ['jobs',     'download'],
            ['attempts', 'force-submit'],
        ],
        'Creator' => [
            ['media',       'presigned-upload'],
            ['media',       'create'],
            ['questions',   'view'],
            ['questions',   'create'],
            ['questions',   'bulk-import'],
            ['questions',   'bulk-update'],
            ['tests',       'view'],
            ['tests',       'create'],
            ['tests',       'update-sections'],
            ['tests',       'publish'],
            ['assignments', 'create'],
            ['ai',          'generate-questions'],
        ],
        'Reviewer' => [
            ['grading',  'view-pending'],
            ['grading',  'review-answer'],
            ['grading',  'finalize'],
            ['reports',  'view'],
            ['attempts', 'force-submit'],
            ['ai',       'suggest-feedback'],
        ],
        'Student' => [
            ['assignments', 'view'],
            ['assignments', 'verify'],
            ['attempts',    'start'],
            ['attempts',    'view'],
            ['attempts',    'heartbeat'],
            ['attempts',    'save-answers'],
            ['attempts',    'resume'],
            ['attempts',    'submit'],
        ],
        'Guest' => [
            ['assignments', 'verify'],
        ],
    ];

    public function run(): void
    {
        foreach ($this->permissions as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'resource' => $resource,
                    'action' => $action,
                ]);
            }
        }

        foreach ($this->roles as $roleName => $permissionPairs) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $this->description($roleName)]
            );

            $permissionIds = collect($permissionPairs)
                ->map(fn (array $pair) => Permission::query()
                    ->where('resource', $pair[0])
                    ->where('action', $pair[1])
                    ->value('id'))
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);
        }

        $this->command->info('Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['System Admin', 'system.health, tenant.settings/manage'],
                ['Admin', 'users.view/create/update-status, roles.view, tenant.*, audit.view, reports.view/export, jobs.download, attempts.force-submit'],
                ['Creator', 'media.presigned-upload/create, questions.view/create/bulk-import/bulk-update, tests.view/create/update-sections/publish, assignments.create, ai.generate-questions'],
                ['Reviewer', 'grading.view-pending/review-answer/finalize, reports.view, attempts.force-submit, ai.suggest-feedback'],
                ['Student', 'assignments.view/verify, attempts.start/view/heartbeat/save-answers/resume/submit'],
                ['Guest', 'assignments.verify'],
            ]
        );
    }

    private function description(string $role): string
    {
        return match ($role) {
            'System Admin' => 'Platform operator focused on system health and tenant lifecycle only.',
            'Admin' => 'Tenant administrator who manages users, roles, audit logs, reports, and exam enforcement.',
            'Creator' => 'Can upload media, manage question bank, build tests, publish tests, and assign exams.',
            'Reviewer' => 'Can review pending submissions, score answers, and finalize grading.',
            'Student' => 'Can verify assignments and complete exam attempts assigned to them.',
            'Guest' => 'Can verify token-linked exam access without a fixed account.',
            default => '',
        };
    }
}

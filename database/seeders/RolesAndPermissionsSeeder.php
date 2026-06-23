<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Role\Models\Role;
use App\Modules\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions: [resource => [action, ...]]
     */
    private array $permissions = [
        'questions'   => ['view', 'create', 'update', 'delete', 'publish', 'archive'],
        'tests'       => ['view', 'build', 'publish'],
        'assignments' => ['manage', 'view'],
        'attempts'    => ['force-submit'],
        'grading'     => ['review'],
        'reports'     => ['view', 'export'],
        'ai'          => ['generate-questions', 'suggest-feedback'],
        'system'      => ['health'],
        'users'       => ['manage'],
        'tenant'      => ['settings', 'manage'],
        'audit'       => ['view'],
    ];

    /**
     * Roles: name => [[resource, action], ...]
     */
    private array $roles = [
        'System Admin' => [
            ['questions',   'view'],
            ['questions',   'create'],
            ['questions',   'update'],
            ['questions',   'delete'],
            ['questions',   'publish'],
            ['questions',   'archive'],
            ['tests',       'view'],
            ['tests',       'build'],
            ['tests',       'publish'],
            ['assignments', 'manage'],
            ['attempts',    'force-submit'],
            ['grading',     'review'],
            ['reports',     'view'],
            ['reports',     'export'],
            ['ai',          'generate-questions'],
            ['ai',          'suggest-feedback'],
            ['system',      'health'],
            ['users',       'manage'],
            ['tenant',      'settings'],
            ['tenant',      'manage'],
            ['audit',       'view'],
        ],
        'Admin' => [
            ['users',       'manage'],
            ['tenant',      'settings'],
            ['tenant',      'manage'],
            ['audit',       'view'],
            ['reports',     'view'],
            ['reports',     'export'],
            ['attempts',    'force-submit'],
        ],
        'Creator' => [
            ['questions', 'view'],
            ['questions', 'create'],
            ['questions', 'update'],
            ['questions', 'delete'],
            ['questions', 'publish'],
            ['questions', 'archive'],
            ['tests',     'view'],
            ['tests',     'build'],
            ['tests',     'publish'],
            ['assignments', 'manage'],
            ['ai', 'generate-questions'],
        ],
        'Reviewer' => [
            ['grading', 'review'],
            ['reports', 'view'],
            ['attempts', 'force-submit'],
            ['ai', 'suggest-feedback'],
        ],
        'Student' => [
            ['assignments', 'view'],
        ],
        'Guest' => [],
    ];

    public function run(): void
    {
        // ─────────────────────────────────────────────
        // 1. Seed permissions
        // ─────────────────────────────────────────────
        foreach ($this->permissions as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'resource' => $resource,
                    'action'   => $action,
                ]);
            }
        }

        // ─────────────────────────────────────────────
        // 2. Seed roles & attach permissions
        // ─────────────────────────────────────────────
        foreach ($this->roles as $roleName => $permissionPairs) {
            $role = Role::firstOrCreate([
                'name'      => $roleName,
            ], [
                'description' => $this->description($roleName),
            ]);

            if (empty($permissionPairs)) {
                continue;
            }

            $permissionIds = collect($permissionPairs)
                ->map(fn ($pair) => Permission::where('resource', $pair[0])
                    ->where('action', $pair[1])
                    ->value('id'))
                ->filter()
                ->all();

            $role->permissions()->sync($permissionIds);
        }

        $this->command->info('Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['System Admin',     'All permissions'],
                ['Admin',            'users.manage, tenant.*, audit.view, reports.view/export'],
                ['Creator',          'questions.*, tests.view/build/publish, assignments.manage, ai.generate-questions'],
                ['Reviewer',         'grading.review, reports.view, attempts.force-submit, ai.suggest-feedback'],
                ['Student',          'assignments.view'],
                ['Guest',            '— (token-linked exam access only)'],
            ]
        );
    }

    private function description(string $role): string
    {
        return match ($role) {
            'System Admin'     => 'Platform operator with full access across tenants and system health.',
            'Admin'            => 'Tenant administrator who manages users, roles, audit logs, and reports.',
            'Creator'          => 'Can manage question bank, build tests, publish tests, and assign exams.',
            'Reviewer'         => 'Can review and grade student submissions.',
            'Student'          => 'Access to assigned tests and own submissions only.',
            'Guest'            => 'Access to token-linked tests and own submissions only.',
            default            => '',
        };
    }
}

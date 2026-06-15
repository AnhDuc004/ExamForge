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
        'questions'   => ['create', 'publish'],
        'tests'       => ['build', 'publish'],
        'assignments' => ['manage'],
        'grading'     => ['review'],
        'users'       => ['manage'],
        'tenant'      => ['settings'],
        'audit'       => ['view'],
    ];

    /**
     * Roles: name => [[resource, action], ...]
     */
    private array $roles = [
        'Tenant Admin' => [
            ['questions',   'create'],
            ['questions',   'publish'],
            ['tests',       'build'],
            ['tests',       'publish'],
            ['assignments', 'manage'],
            ['grading',     'review'],
            ['users',       'manage'],
            ['tenant',      'settings'],
            ['audit',       'view'],
        ],
        'Question Creator' => [
            ['questions', 'create'],
            ['questions', 'publish'],
            ['tests',     'build'],
            ['tests',     'publish'],
        ],
        'Reviewer' => [
            ['grading', 'review'],
        ],
        'Assignee' => [
            ['assignments', 'manage'],
        ],
        'Student' => [],
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

        $this->command->info('✅  Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['Tenant Admin',     'All (9 permissions)'],
                ['Question Creator', 'questions.create, questions.publish, tests.build, tests.publish'],
                ['Reviewer',         'grading.review'],
                ['Assignee',         'assignments.manage'],
                ['Student',          '— (none)'],
            ]
        );
    }

    private function description(string $role): string
    {
        return match ($role) {
            'Tenant Admin'     => 'Full access to all resources within the tenant.',
            'Question Creator' => 'Can create, publish questions and build/publish tests.',
            'Reviewer'         => 'Can review and grade student submissions.',
            'Assignee'         => 'Can manage and assign tests to students.',
            'Student'          => 'Access to assigned tests and own submissions only.',
            default            => '',
        };
    }
}
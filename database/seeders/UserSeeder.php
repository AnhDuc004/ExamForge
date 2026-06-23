<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use App\Modules\User\Models\User;
use App\Modules\Role\Models\Role;
use App\Modules\Tenant\Models\Tenant;

class UserSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = '12345678';

    public function run(): void
    {
        $tenant = Tenant::where('slug', 'default')->first();

        if (!$tenant) {
            $this->command->error('Tenant not found!');
            return;
        }

        $role = Role::where('name', 'Admin')->first();
        $systemAdminRole = Role::where('name', 'System Admin')->first();

        if (!$role) {
            $this->command->error('Role Admin not found!');
            return;
        }

        if (!$systemAdminRole) {
            $this->command->error('Role System Admin not found!');
            return;
        }

        $user = User::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'email' => 'admin@tenant.local',
            ],
            [
                'id'            => (string) Str::uuid(),
                'display_name'  => 'Admin',
                'password_hash' => Hash::make(self::DEFAULT_PASSWORD),
                'tenant_id'     => $tenant->id,
                'is_active'     => true,
            ]
        );

        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'model_type' => \App\Modules\User\Models\User::class,
            ]
        ]);

        $systemAdmin = User::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'email' => 'sysadmin@examforge.local',
            ],
            [
                'id'            => (string) Str::uuid(),
                'display_name'  => 'System Admin',
                'password_hash' => Hash::make(self::DEFAULT_PASSWORD),
                'tenant_id'     => $tenant->id,
                'is_active'     => true,
            ]
        );

        $systemAdmin->roles()->syncWithoutDetaching([
            $systemAdminRole->id => [
                'model_type' => \App\Modules\User\Models\User::class,
            ]
        ]);

        $this->command->info('Admin users created');
        $this->command->table(
            ['Email', 'Role', 'Password', 'Scope'],
            [
                ['sysadmin@examforge.local', 'System Admin', self::DEFAULT_PASSWORD, 'global platform administration'],
                ['admin@tenant.local', 'Admin', self::DEFAULT_PASSWORD, 'default tenant administration'],
            ]
        );
    }
}

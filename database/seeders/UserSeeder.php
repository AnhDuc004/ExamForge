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
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'default')->first();

        if (!$tenant) {
            $this->command->error('Tenant not found!');
            return;
        }

        $role = Role::where('name', 'Admin')->first();

        if (!$role) {
            $this->command->error('Role Admin not found!');
            return;
        }

        $user = User::firstOrCreate(
            [
                'email' => 'admin@tenant.local',
            ],
            [
                'id'            => (string) Str::uuid(),
                'display_name'  => 'Admin',
                'password_hash' => Hash::make('12345678'),
                'tenant_id'     => $tenant->id,
                'is_active'     => true,
            ]
        );

        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'model_type' => \App\Modules\User\Models\User::class,
            ]
        ]);

        $this->command->info('Admin user created');
    }
}

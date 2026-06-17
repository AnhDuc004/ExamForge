<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Tenant\Models\Tenant;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Default Tenant',
                'plan' => 'free',
                'is_active' => true,
            ]
        );

        $this->command->info('Tenant seeded: ' . $tenant->name);
    }
}
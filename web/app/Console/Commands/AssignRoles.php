<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignRoles extends Command
{
    protected $signature = 'app:assign-roles';
    protected $description = 'Assign roles to test users';

    public function handle()
    {
        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'vendor']);

        $admin = User::where('email', 'admin@ticketsout24.com')->first();
        if ($admin && !$admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }

        $vendor = User::where('email', 'vendor@ticketsout24.com')->first();
        if ($vendor && !$vendor->hasRole('vendor')) {
            $vendor->assignRole('vendor');
        }

        $this->info('Roles assigned successfully.');
    }
}

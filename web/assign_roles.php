<?php
use App\Models\User;
use Spatie\Permission\Models\Role;

// Ensure roles exist
Role::firstOrCreate(['name' => 'super-admin']);
Role::firstOrCreate(['name' => 'vendor']);

$admin = User::firstOrCreate(
    ['email' => 'admin@ticketsout24.com'],
    ['name' => 'Admin User', 'password' => bcrypt('password')]
);
if (!$admin->hasRole('super-admin')) {
    $admin->assignRole('super-admin');
}

$vendor = User::firstOrCreate(
    ['email' => 'vendor@ticketsout24.com'],
    ['name' => 'Test Vendor', 'password' => bcrypt('password')]
);
if (!$vendor->hasRole('vendor')) {
    $vendor->assignRole('vendor');
}

echo "Roles assigned successfully.\n";

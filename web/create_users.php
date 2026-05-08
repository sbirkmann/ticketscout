<?php
use App\Models\User;

$admin = User::firstOrCreate(
    ['email' => 'admin@ticketsout24.com'],
    ['name' => 'Admin User', 'password' => bcrypt('password'), 'role' => 'admin']
);

$vendor = User::firstOrCreate(
    ['email' => 'vendor@ticketsout24.com'],
    ['name' => 'Test Vendor', 'password' => bcrypt('password'), 'role' => 'vendor']
);

echo "Users created successfully.\n";

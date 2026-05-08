<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

$roles = ['superadmin', 'vendor', 'customer', 'scanner'];
foreach ($roles as $role) {
    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
}

// Ensure all existing users have the customer role if they have no roles
$users = User::all();
foreach ($users as $user) {
    if ($user->roles()->count() == 0) {
        $user->assignRole('customer');
        $user->role = 'customer';
        $user->save();
    }
}
echo "Roles created and assigned to existing users.\n";

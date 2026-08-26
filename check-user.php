<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->handle($input = new Symfony\Component\Console\Input\ArgvInput, new Symfony\Component\Console\Output\ConsoleOutput);

$user = \App\Models\User::where('email', 'huntergaming5555566@gmail.com')->first();
if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Roles (name): " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Roles (slug): " . $user->roles->pluck('slug')->implode(', ') . "\n";
    echo "isSuperAdmin: " . ($user->isSuperAdmin() ? 'YES' : 'NO') . "\n";
    echo "hasPermission(education_create): " . ($user->hasPermission('education_create') ? 'YES' : 'NO') . "\n";
    
    // Also check super-admin role slug exists
    $superAdminRole = \App\Models\Role::where('slug', 'super-admin')->first();
    echo "super-admin role exists: " . ($superAdminRole ? 'YES (id=' . $superAdminRole->id . ')' : 'NO') . "\n";
}

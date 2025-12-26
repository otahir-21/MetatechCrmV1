<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$email = $argv[1] ?? 'superadmin@productowner.com';
$password = $argv[2] ?? 'Admin123@'; // Change to your actual password

$user = \App\Models\User::where('email', $email)->first();

if (!$user) {
    echo "User not found: {$email}\n";
    exit(1);
}

if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
    echo "Invalid password for: {$email}\n";
    exit(1);
}

$token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
echo "Token for {$email}:\n";
echo $token . "\n";
echo "\nUser ID: {$user->id}\n";
echo "User Role: {$user->role}\n";
echo "Is Product Owner: " . ($user->isProductOwner() ? 'Yes' : 'No') . "\n";


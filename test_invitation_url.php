<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\EmployeeInvitationService;

// Simulate request context
$_SERVER['HTTP_HOST'] = 'crm.localhost:8000';
$_SERVER['SERVER_PORT'] = 8000;
$_SERVER['HTTPS'] = 'off';
$_SERVER['REQUEST_SCHEME'] = 'http';

$service = app(EmployeeInvitationService::class);
$url = $service->getInvitationUrl('test@example.com', 'test_token_123');

echo "Generated URL: " . $url . "\n";
echo "\nExpected format: http://crm.localhost:8000/employee/invite/accept?email=...&token=...\n";


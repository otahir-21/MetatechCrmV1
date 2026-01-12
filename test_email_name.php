<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email to verify sender name shows as "Metatech CRM"', function ($message) {
        $message->to('admin@metatech.ae')
                ->subject('SMTP Test - Sender Name Verification');
    });
    
    echo "✅ Email sent successfully!\n";
    echo "Check your inbox - the sender should now show as 'Metatech CRM' instead of 'Laravel'\n";
} catch (\Exception $e) {
    echo "❌ Error sending email: " . $e->getMessage() . "\n";
}


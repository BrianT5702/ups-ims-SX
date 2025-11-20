<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Default Accounts Created by Seeder ===\n\n";

// Check each database
$databases = ['ups', 'urs', 'ucs'];

foreach ($databases as $db) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Database: " . strtoupper($db) . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    try {
        // Admin Account
        $admin = App\Models\User::on($db)->where('email', 'admin@example.com')->first();
        if ($admin) {
            echo "👤 ADMIN ACCOUNT:\n";
            echo "   Email:    {$admin->email}\n";
            echo "   Username: {$admin->username}\n";
            echo "   Password: admin12345\n";
            echo "   Name:     {$admin->name}\n";
            echo "   Role:     Admin (Full Access)\n\n";
        }
        
        // Regular User Account
        $user = App\Models\User::on($db)->where('email', 'user@example.com')->first();
        if ($user) {
            echo "👤 USER ACCOUNT:\n";
            echo "   Email:    {$user->email}\n";
            echo "   Username: {$user->username}\n";
            echo "   Password: user12345\n";
            echo "   Name:     {$user->name}\n";
            echo "   Role:     User (No default permissions)\n\n";
        }
        
        // Salesperson Accounts
        $salesman1 = App\Models\User::on($db)->where('email', 'salesman1@example.com')->first();
        $salesman2 = App\Models\User::on($db)->where('email', 'salesman2@example.com')->first();
        $salesman3 = App\Models\User::on($db)->where('email', 'salesman3@example.com')->first();
        
        if ($salesman1 || $salesman2 || $salesman3) {
            echo "👤 SALESPERSON ACCOUNTS:\n";
            if ($salesman1) {
                echo "   Salesman 1:\n";
                echo "      Email:    {$salesman1->email}\n";
                echo "      Username: {$salesman1->username}\n";
                echo "      Password: salesman12345\n";
                echo "      Name:     {$salesman1->name}\n\n";
            }
            if ($salesman2) {
                echo "   Salesman 2:\n";
                echo "      Email:    {$salesman2->email}\n";
                echo "      Username: {$salesman2->username}\n";
                echo "      Password: salesman12345\n";
                echo "      Name:     {$salesman2->name}\n\n";
            }
            if ($salesman3) {
                echo "   Salesman 3:\n";
                echo "      Email:    {$salesman3->email}\n";
                echo "      Username: {$salesman3->username}\n";
                echo "      Password: salesman12345\n";
                echo "      Name:     {$salesman3->name}\n\n";
            }
        }
        
        echo "Total users in " . strtoupper($db) . ": " . App\Models\User::on($db)->count() . "\n";
        
    } catch (\Exception $e) {
        echo "⚠️  Error accessing {$db} database: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📝 QUICK LOGIN REFERENCE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "🌐 Application URL: http://localhost:8000\n\n";
echo "🔐 RECOMMENDED LOGIN (Admin - Full Access):\n";
echo "   Email:    admin@example.com\n";
echo "   Password: admin12345\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";










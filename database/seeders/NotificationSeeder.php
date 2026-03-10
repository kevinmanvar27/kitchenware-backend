<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users
        $adminUsers = User::where('user_role', 'admin')->orWhere('user_role', 'super_admin')->get();
        
        // Create sample notifications for each admin user
        foreach ($adminUsers as $adminUser) {
            Notification::create([
                'user_id' => $adminUser->id,
                'title' => 'New Proforma Invoice Created',
                'message' => 'A new proforma invoice #INV-001 has been created by John Doe',
                'type' => 'proforma_invoice',
                'data' => json_encode([
                    'invoice_id' => 1,
                    'invoice_number' => 'INV-001',
                    'customer_name' => 'John Doe',
                    'customer_avatar' => null
                ]),
                'read' => false,
            ]);
            
            Notification::create([
                'user_id' => $adminUser->id,
                'title' => 'Settings Updated',
                'message' => 'System settings have been updated by administrator',
                'type' => 'settings_updated',
                'data' => json_encode([]),
                'read' => false,
            ]);
        }
    }
}
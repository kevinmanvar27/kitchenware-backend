<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setting = Setting::first();
        if (!$setting) {
            Setting::create([
                'site_title' => 'Hardware Store',
                'site_description' => 'Your one-stop shop for all hardware needs',
                'footer_text' => '© 2025 Hardware Store. All rights reserved.',
                'theme_color' => '#FF6B00',
                'background_color' => '#FFFFFF',
                'font_color' => '#333333',
                'font_style' => 'Arial, sans-serif',
                'sidebar_text_color' => '#333333',
                'heading_text_color' => '#333333',
                'label_text_color' => '#333333',
                'general_text_color' => '#333333',
                'header_logo' => null,
                'footer_logo' => null,
                'favicon' => null,
                'facebook_url' => null,
                'twitter_url' => null,
                'instagram_url' => null,
                'linkedin_url' => null,
                'youtube_url' => null,
                'whatsapp_url' => null,
                // Payment settings
                'razorpay_key_id' => null,
                'razorpay_key_secret' => null,
                // App store links
                'app_store_link' => null,
                'play_store_link' => null,
                // Firebase settings
                'firebase_project_id' => null,
                'firebase_client_email' => null,
                'firebase_private_key' => null,
                // Site Management defaults
                'maintenance_mode' => false,
                'maintenance_end_time' => null,
                'maintenance_message' => 'We are currently under maintenance. The website will be back online approximately at {end_time}.',
                'coming_soon_mode' => false,
                'launch_time' => null,
                'coming_soon_message' => "We're launching soon! Our amazing platform will be available at {launch_time}.",
            ]);
        } else {
            // Update existing record with new fields
            $setting->update([
                'sidebar_text_color' => '#333333',
                'heading_text_color' => '#333333',
                'label_text_color' => '#333333',
                'general_text_color' => '#333333',
                // Payment settings
                'razorpay_key_id' => null,
                'razorpay_key_secret' => null,
                // App store links
                'app_store_link' => null,
                'play_store_link' => null,
                // Firebase settings
                'firebase_project_id' => null,
                'firebase_client_email' => null,
                'firebase_private_key' => null,
                // Site Management defaults
                'maintenance_mode' => false,
                'maintenance_end_time' => null,
                'maintenance_message' => 'We are currently under maintenance. The website will be back online approximately at {end_time}.',
                'coming_soon_mode' => false,
                'launch_time' => null,
                'coming_soon_message' => "We're launching soon! Our amazing platform will be available at {launch_time}.",
            ]);
        }
    }
}
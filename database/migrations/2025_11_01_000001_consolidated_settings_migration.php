<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                
                // General settings
                $table->string('header_logo')->nullable();
                $table->string('footer_logo')->nullable();
                $table->string('favicon')->nullable();
                $table->string('site_title')->nullable();
                $table->text('site_description')->nullable();
                $table->string('tagline')->nullable();
                $table->text('footer_text')->nullable();
                $table->string('theme_color')->nullable();
                $table->string('background_color')->nullable();
                
                // Font settings
                $table->string('font_color')->nullable();
                $table->string('font_style')->nullable();
                
                // Text color settings
                $table->string('sidebar_text_color')->nullable();
                $table->string('heading_text_color')->nullable();
                $table->string('label_text_color')->nullable();
                $table->string('general_text_color')->nullable();
                
                // Link color settings
                $table->string('link_color')->nullable();
                $table->string('link_hover_color')->nullable();
                
                // Social media settings
                $table->string('facebook_url')->nullable();
                $table->string('twitter_url')->nullable();
                $table->string('instagram_url')->nullable();
                $table->string('linkedin_url')->nullable();
                $table->string('youtube_url')->nullable();
                $table->string('whatsapp_url')->nullable();
                
                // App store links
                $table->string('app_store_link')->nullable();
                $table->string('play_store_link')->nullable();
                
                // Maintenance Mode fields
                $table->boolean('maintenance_mode')->default(false);
                $table->timestamp('maintenance_end_time')->nullable();
                $table->text('maintenance_message')->nullable();
                
                // Coming Soon Mode fields
                $table->boolean('coming_soon_mode')->default(false);
                $table->timestamp('launch_time')->nullable();
                $table->text('coming_soon_message')->nullable();
                
                // Payment settings
                $table->string('razorpay_key_id')->nullable();
                $table->string('razorpay_key_secret')->nullable();
                
                // Firebase Cloud Messaging settings
                $table->string('firebase_project_id')->nullable();
                $table->string('firebase_client_email')->nullable();
                $table->text('firebase_private_key')->nullable();
                
                $table->timestamps();
            });
        } else {
            // Table exists, check and add any missing columns
            Schema::table('settings', function (Blueprint $table) {
                // General settings
                if (!Schema::hasColumn('settings', 'header_logo')) {
                    $table->string('header_logo')->nullable();
                }
                if (!Schema::hasColumn('settings', 'footer_logo')) {
                    $table->string('footer_logo')->nullable();
                }
                if (!Schema::hasColumn('settings', 'favicon')) {
                    $table->string('favicon')->nullable();
                }
                if (!Schema::hasColumn('settings', 'site_title')) {
                    $table->string('site_title')->nullable();
                }
                if (!Schema::hasColumn('settings', 'site_description')) {
                    $table->text('site_description')->nullable();
                }
                if (!Schema::hasColumn('settings', 'tagline')) {
                    $table->string('tagline')->nullable();
                }
                if (!Schema::hasColumn('settings', 'footer_text')) {
                    $table->text('footer_text')->nullable();
                }
                if (!Schema::hasColumn('settings', 'theme_color')) {
                    $table->string('theme_color')->nullable();
                }
                if (!Schema::hasColumn('settings', 'background_color')) {
                    $table->string('background_color')->nullable();
                }
                
                // Font settings
                if (!Schema::hasColumn('settings', 'font_color')) {
                    $table->string('font_color')->nullable();
                }
                if (!Schema::hasColumn('settings', 'font_style')) {
                    $table->string('font_style')->nullable();
                }
                
                // Text color settings
                if (!Schema::hasColumn('settings', 'sidebar_text_color')) {
                    $table->string('sidebar_text_color')->nullable();
                }
                if (!Schema::hasColumn('settings', 'heading_text_color')) {
                    $table->string('heading_text_color')->nullable();
                }
                if (!Schema::hasColumn('settings', 'label_text_color')) {
                    $table->string('label_text_color')->nullable();
                }
                if (!Schema::hasColumn('settings', 'general_text_color')) {
                    $table->string('general_text_color')->nullable();
                }
                
                // Link color settings
                if (!Schema::hasColumn('settings', 'link_color')) {
                    $table->string('link_color')->nullable();
                }
                if (!Schema::hasColumn('settings', 'link_hover_color')) {
                    $table->string('link_hover_color')->nullable();
                }
                
                // Social media settings
                if (!Schema::hasColumn('settings', 'facebook_url')) {
                    $table->string('facebook_url')->nullable();
                }
                if (!Schema::hasColumn('settings', 'twitter_url')) {
                    $table->string('twitter_url')->nullable();
                }
                if (!Schema::hasColumn('settings', 'instagram_url')) {
                    $table->string('instagram_url')->nullable();
                }
                if (!Schema::hasColumn('settings', 'linkedin_url')) {
                    $table->string('linkedin_url')->nullable();
                }
                if (!Schema::hasColumn('settings', 'youtube_url')) {
                    $table->string('youtube_url')->nullable();
                }
                if (!Schema::hasColumn('settings', 'whatsapp_url')) {
                    $table->string('whatsapp_url')->nullable();
                }
                
                // App store links
                if (!Schema::hasColumn('settings', 'app_store_link')) {
                    $table->string('app_store_link')->nullable();
                }
                if (!Schema::hasColumn('settings', 'play_store_link')) {
                    $table->string('play_store_link')->nullable();
                }
                
                // Maintenance Mode fields
                if (!Schema::hasColumn('settings', 'maintenance_mode')) {
                    $table->boolean('maintenance_mode')->default(false);
                }
                if (!Schema::hasColumn('settings', 'maintenance_end_time')) {
                    $table->timestamp('maintenance_end_time')->nullable();
                }
                if (!Schema::hasColumn('settings', 'maintenance_message')) {
                    $table->text('maintenance_message')->nullable();
                }
                
                // Coming Soon Mode fields
                if (!Schema::hasColumn('settings', 'coming_soon_mode')) {
                    $table->boolean('coming_soon_mode')->default(false);
                }
                if (!Schema::hasColumn('settings', 'launch_time')) {
                    $table->timestamp('launch_time')->nullable();
                }
                if (!Schema::hasColumn('settings', 'coming_soon_message')) {
                    $table->text('coming_soon_message')->nullable();
                }
                
                // Payment settings
                if (!Schema::hasColumn('settings', 'razorpay_key_id')) {
                    $table->string('razorpay_key_id')->nullable();
                }
                if (!Schema::hasColumn('settings', 'razorpay_key_secret')) {
                    $table->string('razorpay_key_secret')->nullable();
                }
                
                // Firebase Cloud Messaging settings
                if (!Schema::hasColumn('settings', 'firebase_project_id')) {
                    $table->string('firebase_project_id')->nullable();
                }
                if (!Schema::hasColumn('settings', 'firebase_client_email')) {
                    $table->string('firebase_client_email')->nullable();
                }
                if (!Schema::hasColumn('settings', 'firebase_private_key')) {
                    $table->text('firebase_private_key')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
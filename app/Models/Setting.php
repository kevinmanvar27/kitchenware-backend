<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'maintenance_mode' => 'boolean',
        'coming_soon_mode' => 'boolean',
        'show_online_payment' => 'boolean',
        'show_cod_payment' => 'boolean',
        'show_invoice_payment' => 'boolean',
        'maintenance_end_time' => 'datetime',
        'launch_time' => 'datetime',
    ];

    protected $fillable = [
        'header_logo',
        'footer_logo',
        'favicon',
        'site_title',
        'site_description',
        'tagline',
        'footer_text',
        'address',
        'company_email',
        'company_phone',
        'gst_number',
        'authorized_signatory',
        'theme_color',
        'background_color',
        'font_color',
        'font_style',
        'sidebar_text_color',
        'heading_text_color',
        'label_text_color',
        'general_text_color',
        'link_color',
        'link_hover_color',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'whatsapp_url',
        'maintenance_mode',
        'maintenance_end_time',
        'maintenance_message',
        'coming_soon_mode',
        'launch_time',
        'coming_soon_message',
        'razorpay_key_id',
        'razorpay_key_secret',
        'app_store_link',
        'play_store_link',
        'firebase_project_id',
        'firebase_client_email',
        'firebase_private_key',
        // Font family settings (element-wise)
        'h1_font_family',
        'h2_font_family',
        'h3_font_family',
        'h4_font_family',
        'h5_font_family',
        'h6_font_family',
        'body_font_family',
        // Font size settings
        'desktop_h1_size',
        'desktop_h2_size',
        'desktop_h3_size',
        'desktop_h4_size',
        'desktop_h5_size',
        'desktop_h6_size',
        'desktop_body_size',
        'tablet_h1_size',
        'tablet_h2_size',
        'tablet_h3_size',
        'tablet_h4_size',
        'tablet_h5_size',
        'tablet_h6_size',
        'tablet_body_size',
        'mobile_h1_size',
        'mobile_h2_size',
        'mobile_h3_size',
        'mobile_h4_size',
        'mobile_h5_size',
        'mobile_h6_size',
        'mobile_body_size',
        'frontend_access_permission',
        'pending_approval_message',
        'show_online_payment',
        'show_cod_payment',
        'show_invoice_payment',
        // RazorpayX settings for vendor payouts
        'razorpayx_key_id',
        'razorpayx_key_secret',
        'razorpayx_account_number',
        'razorpayx_webhook_secret',
        'razorpayx_mode',
    ];
}
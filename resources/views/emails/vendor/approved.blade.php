<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Vendor Store Approved - {{ config('app.name') }}</title>
    <style>
        /* Reset */
        body, table, td, p, a, li { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #374151;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .email-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 40px 40px 30px;
            text-align: center;
        }
        .brand-name {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }
        .brand-tagline {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            margin: 0;
            font-weight: 500;
        }
        .status-icon {
            width: 80px;
            height: 80px;
            margin: 20px auto 15px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            line-height: 80px;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: rgba(255, 255, 255, 0.25);
            color: #ffffff;
            margin-top: 10px;
        }
        .email-content {
            padding: 40px;
        }
        .greeting {
            font-size: 22px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 20px 0;
        }
        .email-content p {
            margin: 0 0 16px 0;
            color: #4b5563;
        }
        .alert-box {
            border-radius: 10px;
            padding: 16px 20px;
            margin: 24px 0;
            display: flex;
            align-items: flex-start;
        }
        .alert-box.success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
        }
        .alert-box.warning {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
        }
        .alert-icon {
            font-size: 20px;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .alert-content { flex: 1; }
        .alert-title {
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 4px 0;
        }
        .alert-box.success .alert-title { color: #065f46; }
        .alert-box.warning .alert-title { color: #92400e; }
        .alert-text {
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }
        .alert-box.success .alert-text { color: #047857; }
        .alert-box.warning .alert-text { color: #b45309; }
        .credentials-box {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            color: #ffffff;
        }
        .credentials-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .credentials-icon {
            font-size: 20px;
            margin-right: 10px;
        }
        .credentials-title {
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
        }
        .credential-item {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 10px;
        }
        .credential-item:last-child { margin-bottom: 0; }
        .credential-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 4px;
        }
        .credential-value {
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            word-break: break-all;
        }
        .info-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }
        .info-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-card-icon {
            font-size: 20px;
            margin-right: 10px;
        }
        .info-card-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        .info-value {
            font-size: 14px;
            color: #111827;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
            max-width: 60%;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            text-align: center;
            margin: 28px 0 16px 0;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: 24px 0;
        }
        .feature-item {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }
        .feature-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .feature-title {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin: 0;
        }
        .button-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            margin: 6px;
        }
        .btn-primary {
            background-color: #10b981;
            color: #ffffff !important;
        }
        .btn-secondary {
            background-color: #3b82f6;
            color: #ffffff !important;
        }
        .signature {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        .signature p {
            margin: 0 0 4px 0;
            color: #4b5563;
        }
        .signature-name {
            font-weight: 600;
            color: #111827 !important;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-brand {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 8px 0;
        }
        .footer-text {
            font-size: 13px;
            color: #6b7280;
            margin: 0 0 4px 0;
        }
        .footer-note {
            font-size: 12px;
            color: #9ca3af;
            margin: 16px 0 0 0;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .email-header { padding: 30px 24px 24px; }
            .email-content { padding: 30px 24px; }
            .email-footer { padding: 24px; }
            .brand-name { font-size: 24px; }
            .greeting { font-size: 18px; }
            .features-grid { grid-template-columns: 1fr; }
            .info-row { flex-direction: column; align-items: flex-start; }
            .info-value { text-align: left; max-width: 100%; margin-top: 4px; }
            .btn { display: block; margin: 8px 0; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="brand-name">{{ config('app.name') }}</h1>
                <p class="brand-tagline">Vendor Portal</p>
                <div class="status-icon">🎉</div>
                <span class="status-badge">✓ Approved</span>
            </div>
            
            <div class="email-content">
                <h2 class="greeting">Congratulations, {{ $user->name }}!</h2>
                
                <p>Great news! Your vendor application has been <strong>approved</strong>. Your store "<strong>{{ $vendor->store_name }}</strong>" is now live and ready to accept orders!</p>
                
                <div class="alert-box success">
                    <span class="alert-icon">🚀</span>
                    <div class="alert-content">
                        <p class="alert-title">Your store is now active!</p>
                        <p class="alert-text">You can start adding products and managing your business right away.</p>
                    </div>
                </div>
                
                <div class="credentials-box">
                    <div class="credentials-header">
                        <span class="credentials-icon">🔐</span>
                        <h3 class="credentials-title">Your Login Credentials</h3>
                    </div>
                    <div class="credential-item">
                        <div class="credential-label">Login URL</div>
                        <div class="credential-value">{{ url('/vendor/login') }}</div>
                    </div>
                    <div class="credential-item">
                        <div class="credential-label">Email Address</div>
                        <div class="credential-value">{{ $user->email }}</div>
                    </div>
                    @if($plainPassword)
                    <div class="credential-item">
                        <div class="credential-label">Password</div>
                        <div class="credential-value">{{ $plainPassword }}</div>
                    </div>
                    @else
                    <div class="credential-item">
                        <div class="credential-label">Password</div>
                        <div class="credential-value">Use the password you set during registration</div>
                    </div>
                    @endif
                </div>
                
                @if($plainPassword)
                <div class="alert-box warning">
                    <span class="alert-icon">⚠️</span>
                    <div class="alert-content">
                        <p class="alert-title">Security Notice</p>
                        <p class="alert-text">For your security, please change your password immediately after your first login. Never share your login credentials with anyone.</p>
                    </div>
                </div>
                @endif
                
                <div class="info-card">
                    <div class="info-card-header">
                        <span class="info-card-icon">🏪</span>
                        <h3 class="info-card-title">Store Details</h3>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Store Name</span>
                        <span class="info-value">{{ $vendor->store_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Store URL</span>
                        <span class="info-value">{{ url('/store/' . $vendor->store_slug) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Business Email</span>
                        <span class="info-value">{{ $vendor->business_email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Business Phone</span>
                        <span class="info-value">{{ $vendor->business_phone }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Commission Rate</span>
                        <span class="info-value">{{ $vendor->commission_rate }}%</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Approved On</span>
                        <span class="info-value">{{ $vendor->approved_at ? $vendor->approved_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
                
                <h3 class="section-title">What you can do now</h3>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">📦</div>
                        <p class="feature-title">Add Products</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📊</div>
                        <p class="feature-title">View Analytics</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🛒</div>
                        <p class="feature-title">Manage Orders</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">💰</div>
                        <p class="feature-title">Track Earnings</p>
                    </div>
                </div>
                
                <div class="button-container">
                    <a href="{{ url('/vendor/login') }}" class="btn btn-primary">Login to Vendor Panel</a>
                    <a href="{{ url('/store/' . $vendor->store_slug) }}" class="btn btn-secondary">View Your Store</a>
                </div>
                
                <p>If you have any questions or need assistance setting up your store, our support team is here to help. Welcome aboard and happy selling!</p>
                
                <div class="signature">
                    <p>Best regards,</p>
                    <p class="signature-name">The {{ config('app.name') }} Team</p>
                </div>
            </div>
            
            <div class="email-footer">
                <p class="footer-brand">{{ config('app.name') }}</p>
                <p class="footer-text">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p class="footer-note">This is an automated message. Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>

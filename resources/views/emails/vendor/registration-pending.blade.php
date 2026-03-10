<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Vendor Registration Under Review - {{ config('app.name') }}</title>
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
            margin: 28px 0 16px 0;
        }
        /* Timeline */
        .timeline {
            margin: 24px 0;
            padding: 0;
            list-style: none;
        }
        .timeline-item {
            position: relative;
            padding-left: 36px;
            padding-bottom: 24px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: #10b981;
            border: 3px solid #d1fae5;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 7px;
            top: 24px;
            width: 2px;
            height: calc(100% - 16px);
            background-color: #e5e7eb;
        }
        .timeline-item:last-child::after {
            display: none;
        }
        .timeline-item.pending::before {
            background-color: #f59e0b;
            border-color: #fef3c7;
        }
        .timeline-item.upcoming::before {
            background-color: #e5e7eb;
            border-color: #f3f4f6;
        }
        .timeline-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 4px 0;
        }
        .timeline-desc {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }
        .alert-box {
            border-radius: 10px;
            padding: 16px 20px;
            margin: 24px 0;
            display: flex;
            align-items: flex-start;
        }
        .alert-box.info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
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
            color: #1e40af;
        }
        .alert-text {
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
            color: #1d4ed8;
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
            .info-row { flex-direction: column; align-items: flex-start; }
            .info-value { text-align: left; max-width: 100%; margin-top: 4px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="brand-name">{{ config('app.name') }}</h1>
                <p class="brand-tagline">Vendor Registration</p>
                <div class="status-icon">🕐</div>
                <span class="status-badge">Under Review</span>
            </div>
            
            <div class="email-content">
                <h2 class="greeting">Hello, {{ $user->name }}!</h2>
                
                <p>Thank you for registering as a vendor on <strong>{{ config('app.name') }}</strong>! We're excited to have you join our marketplace.</p>
                
                <p>Your vendor application has been successfully submitted and is currently under review by our team. We typically review applications within <strong>24-48 hours</strong>.</p>
                
                <div class="info-card">
                    <div class="info-card-header">
                        <span class="info-card-icon">📋</span>
                        <h3 class="info-card-title">Application Details</h3>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Store Name</span>
                        <span class="info-value">{{ $vendor->store_name }}</span>
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
                        <span class="info-label">Location</span>
                        <span class="info-value">{{ $vendor->city }}, {{ $vendor->state }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Submitted On</span>
                        <span class="info-value">{{ $vendor->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
                
                <h3 class="section-title">What happens next?</h3>
                
                <ul class="timeline">
                    <li class="timeline-item">
                        <p class="timeline-title">Application Submitted</p>
                        <p class="timeline-desc">Your registration is complete</p>
                    </li>
                    <li class="timeline-item pending">
                        <p class="timeline-title">Under Review</p>
                        <p class="timeline-desc">Our team is reviewing your application</p>
                    </li>
                    <li class="timeline-item upcoming">
                        <p class="timeline-title">Approval Notification</p>
                        <p class="timeline-desc">You'll receive an email once approved</p>
                    </li>
                    <li class="timeline-item upcoming">
                        <p class="timeline-title">Start Selling</p>
                        <p class="timeline-desc">Set up your store and list products</p>
                    </li>
                </ul>
                
                <div class="alert-box info">
                    <span class="alert-icon">💡</span>
                    <div class="alert-content">
                        <p class="alert-title">Pro Tip</p>
                        <p class="alert-text">While waiting for approval, you can prepare your product images, descriptions, and pricing information to get started quickly once your store is approved.</p>
                    </div>
                </div>
                
                <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                
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

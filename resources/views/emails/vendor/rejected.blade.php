<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Vendor Application Update - {{ config('app.name') }}</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        .reason-box {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 0 10px 10px 0;
            padding: 20px;
            margin: 24px 0;
        }
        .reason-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .reason-icon {
            font-size: 18px;
            margin-right: 8px;
        }
        .reason-title {
            font-size: 15px;
            font-weight: 600;
            color: #991b1b;
            margin: 0;
        }
        .reason-text {
            font-size: 14px;
            color: #b91c1c;
            margin: 0;
            line-height: 1.6;
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
        .next-steps {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 0 10px 10px 0;
            padding: 20px;
            margin: 24px 0;
        }
        .next-steps-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
        .next-steps-icon {
            font-size: 18px;
            margin-right: 8px;
        }
        .next-steps-title {
            font-size: 15px;
            font-weight: 600;
            color: #1e40af;
            margin: 0;
        }
        .next-steps-list {
            margin: 0;
            padding-left: 20px;
        }
        .next-steps-list li {
            font-size: 14px;
            color: #1d4ed8;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .next-steps-list li:last-child {
            margin-bottom: 0;
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
                <p class="brand-tagline">Vendor Application Update</p>
                <div class="status-icon">📋</div>
                <span class="status-badge">Not Approved</span>
            </div>
            
            <div class="email-content">
                <h2 class="greeting">Hello, {{ $user->name }}</h2>
                
                <p>Thank you for your interest in becoming a vendor on <strong>{{ config('app.name') }}</strong>.</p>
                
                <p>After careful review, we regret to inform you that your vendor application for "<strong>{{ $vendor->store_name }}</strong>" could not be approved at this time.</p>
                
                @if($rejectionReason)
                <div class="reason-box">
                    <div class="reason-header">
                        <span class="reason-icon">📋</span>
                        <h4 class="reason-title">Reason for Decision</h4>
                    </div>
                    <p class="reason-text">{{ $rejectionReason }}</p>
                </div>
                @endif
                
                <div class="info-card">
                    <div class="info-card-header">
                        <span class="info-card-icon">📄</span>
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
                        <span class="info-label">Submitted On</span>
                        <span class="info-value">{{ $vendor->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Reviewed On</span>
                        <span class="info-value">{{ now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
                
                <div class="next-steps">
                    <div class="next-steps-header">
                        <span class="next-steps-icon">💡</span>
                        <h4 class="next-steps-title">What You Can Do Next</h4>
                    </div>
                    <ul class="next-steps-list">
                        <li>Review the reason provided above and address any issues mentioned</li>
                        <li>Ensure all your business information is accurate and complete</li>
                        <li>Contact our support team if you have questions about the decision</li>
                        <li>You may reapply after addressing the concerns raised</li>
                    </ul>
                </div>
                
                <p>We encourage you to address the issues mentioned and consider reapplying. Our team is here to help you through the process.</p>
                
                <div class="button-container">
                    <a href="mailto:{{ config('mail.from.address') }}" class="btn btn-primary">Contact Support</a>
                </div>
                
                <p>We appreciate your understanding and hope to work with you in the future.</p>
                
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

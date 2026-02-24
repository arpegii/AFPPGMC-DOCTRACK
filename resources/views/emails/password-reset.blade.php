<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa; color: #333333; line-height: 1.6; }
        .email-wrapper { width: 100%; background-color: #f4f7fa; padding: 40px 20px; }
        .email-container { max-width: 620px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08); }
        .email-header { background: linear-gradient(135deg, #0b1f3a 0%, #1a3a5c 100%); padding: 36px 28px; text-align: center; color: #ffffff; }
        .email-header h1 { margin: 0; font-size: 26px; font-weight: 600; letter-spacing: -0.3px; }
        .email-header p { margin-top: 10px; font-size: 14px; opacity: 0.9; }
        .email-body { padding: 34px 28px; }
        .greeting { font-size: 20px; font-weight: 600; color: #0b1f3a; margin-bottom: 16px; }
        .message { font-size: 15px; color: #4b5563; margin-bottom: 20px; }
        .notice-box { background: #f8fafc; border: 1px solid #e5e7eb; border-left: 4px solid #0b1f3a; border-radius: 8px; padding: 14px 16px; margin: 20px 0; }
        .notice-label { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 4px; }
        .notice-value { font-size: 14px; color: #0f172a; }
        .cta-wrap { text-align: center; margin: 28px 0 24px; }
        .cta-button { display: inline-block; background-color: #0b1f3a; color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 600; padding: 12px 22px; border-radius: 8px; }
        .meta { margin-top: 10px; font-size: 13px; color: #6b7280; text-align: center; }
        .fallback { margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        .fallback p { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
        .fallback a { font-size: 13px; color: #2563eb; word-break: break-all; text-decoration: none; }
        .email-footer { background-color: #f8f9fc; border-top: 1px solid #e5e7eb; padding: 22px 28px; text-align: center; }
        .email-footer p { font-size: 12px; color: #6b7280; margin: 5px 0; }
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .email-header, .email-body, .email-footer { padding: 24px 18px; }
            .email-header h1 { font-size: 22px; }
            .greeting { font-size: 18px; }
            .cta-button { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>Reset Your Password</h1>
                <p>AFPPGMC Document Tracking System</p>
            </div>

            <div class="email-body">
                <div class="greeting">Hello {{ $name }}!</div>

                <p class="message">
                    We received a request to reset your account password. Click the button below to create a new password.
                </p>

                <div class="notice-box">
                    <div class="notice-label">Security Notice</div>
                    <div class="notice-value">This password reset link expires in {{ $expireMinutes }} minutes.</div>
                </div>

                <div class="cta-wrap">
                    <a href="{{ $resetUrl }}" class="cta-button">Reset Password</a>
                </div>

                <div class="fallback">
                    <p>If the button above does not work, copy and paste this link into your browser:</p>
                    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                </div>

                <p class="message" style="margin-top: 18px;">
                    If you did not request a password reset, no action is needed. Your password will remain unchanged.
                </p>
            </div>

            <div class="email-footer">
                <p>This is an automated security message. Please do not reply.</p>
                <p>AFPPGMC Document Tracking System</p>
            </div>
        </div>
    </div>
</body>
</html>

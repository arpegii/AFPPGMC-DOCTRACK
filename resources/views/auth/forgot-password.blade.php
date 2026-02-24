<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - {{ config('app.name', 'AFPPGMC Document Tracking System') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --color-primary: #1e5ba8;
            --color-bg: #FDFDFC;
            --color-border: #e3e3e0;
            --color-border-hover: #1e5ba835;
            --color-text: #1b1b18;
            --color-text-muted: #706f6c;
            --color-accent: #ffd700;
            --color-error: #dc2626;
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                --color-primary: #2b7fd8;
                --color-bg: #0a0a0a;
                --color-border: #3E3E3A;
                --color-border-hover: #62605b;
                --color-text: #EDEDEC;
                --color-text-muted: #A1A09A;
                --color-accent: #ffd700;
                --color-error: #ef4444;
            }
        }
        
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            line-height: 1.5;
        }
        
        .auth-container {
            width: 100%;
            max-width: 28rem;
            opacity: 0;
            transform: translateY(1rem);
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auth-card {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: 0.375rem;
            overflow: hidden;
            box-shadow: 0px 0px 1px 0px rgba(0,0,0,0.03), 0px 1px 2px 0px rgba(0,0,0,0.06);
        }
        
        .auth-header {
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid var(--color-border);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .logo-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .logo-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }
        
        .logo-text h1 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-text);
            letter-spacing: -0.01em;
        }
        
        .logo-text p {
            font-size: 0.8125rem;
            color: var(--color-text-muted);
            font-weight: 500;
        }
        
        .auth-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.25rem;
        }
        
        .auth-header p {
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        
        .alert-success {
            background: #f0fdf4;
            border-color: #86efac;
            color: #166534;
        }
        
        @media (prefers-color-scheme: dark) {
            .alert-success {
                background: #052e16;
                border-color: #166534;
                color: #86efac;
            }
        }
        
        .alert-success-inner {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert-success svg {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }
        
        .alert-success-text p:first-child {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .alert-success-text p:last-child {
            font-size: 0.8125rem;
        }
        
        .info-box {
            padding: 0.875rem 1rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--color-border);
            background: var(--color-bg);
        }
        
        .info-box-inner {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .info-box svg {
            width: 1rem;
            height: 1rem;
            color: var(--color-text-muted);
            flex-shrink: 0;
            margin-top: 0.125rem;
        }
        
        .info-box-text {
            font-size: 0.8125rem;
            color: var(--color-text-muted);
        }
        
        .info-box-text p:first-child {
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.25rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }
        
        .input-field {
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            border: 1px solid var(--color-border);
            border-radius: 0.25rem;
            background: var(--color-bg);
            color: var(--color-text);
            transition: all 0.15s ease;
            font-family: inherit;
        }
        
        .input-field:hover:not(:focus) {
            border-color: var(--color-border-hover);
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 1px var(--color-primary);
        }
        
        .input-field::placeholder {
            color: var(--color-text-muted);
            opacity: 0.6;
        }
        
        .error-message {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-top: 0.5rem;
            font-size: 0.8125rem;
            color: var(--color-error);
        }
        
        .btn {
            width: 100%;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: var(--color-bg);
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .divider {
            margin: 1.5rem 0;
            padding-top: 1.5rem;
            border-top: 1px solid var(--color-border);
            text-align: center;
        }
        
        .link {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text);
            text-decoration: none;
            transition: color 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .link:hover {
            color: var(--color-accent);
        }
        
        .link svg {
            width: 0.875rem;
            height: 0.875rem;
        }
        
        .auth-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--color-border);
            text-align: center;
        }
        
        .auth-footer p {
            font-size: 0.875rem;
            color: var(--color-text-muted);
        }
        
        .auth-footer .link {
            color: var(--color-text);
            font-weight: 500;
        }
        
        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }
            
            .auth-header,
            .auth-body,
            .auth-footer {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="logo-section">
                    <div class="logo-icon">
                        <img src="images/logo.png" alt="AFPPGMC Logo" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div class="logo-text">
                        <h1>AFPPGMC Document Tracking System</h1>
                        <p>Document Management</p>
                    </div>
                </div>
                <h2>Reset your password</h2>
                <p>Enter your email address to receive reset instructions</p>
            </div>

            <!-- Body -->
            <div class="auth-body">
                @if (session('status'))
                    <div class="alert alert-success">
                        <div class="alert-success-inner">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <div class="alert-success-text">
                                <p>Email sent successfully</p>
                                <p>{{ session('status') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="info-box">
                    <div class="info-box-inner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div class="info-box-text">
                            <p>Password reset process</p>
                            <p>You'll receive an email with a secure link to create a new password. The link will expire in 60 minutes.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">Email address</label>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            autocomplete="email"
                            class="input-field"
                            placeholder="name@company.com"
                        >
                        @error('email')
                            <div class="error-message">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="7" cy="7" r="6"></circle>
                                    <line x1="7" y1="4" x2="7" y2="7"></line>
                                    <line x1="7" y1="10" x2="7.01" y2="10"></line>
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <span>Send reset link</span>
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="1" y1="7" x2="13" y2="1"></line>
                            <polyline points="13 1 13 7 8 13"></polyline>
                        </svg>
                    </button>
                </form>

                <div class="divider">
                    <a href="{{ route('login') }}" class="link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Back to sign in</span>
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
            </div>
        </div>
    </div>
</body>
</html>

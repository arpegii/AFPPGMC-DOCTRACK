<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <style>
        /* Use the same CSS from your register page */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --color-primary: #1e5ba8;
            --color-bg: #FDFDFC;
            --color-border: #e3e3e0;
            --color-text: #1b1b18;
            --color-text-muted: #706f6c;
            --color-success: #2d7a3e;
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                --color-primary: #2b7fd8;
                --color-bg: #0a0a0a;
                --color-border: #3E3E3A;
                --color-text: #EDEDEC;
                --color-text-muted: #A1A09A;
                --color-success: #3d9950;
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
            padding: 2rem 1.5rem;
            line-height: 1.5;
        }
        
        .verify-container {
            width: 100%;
            max-width: 32rem;
            text-align: center;
        }
        
        .verify-card {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: 0.375rem;
            padding: 2.5rem 2rem;
            box-shadow: 0px 0px 1px 0px rgba(0,0,0,0.03), 0px 1px 2px 0px rgba(0,0,0,0.06);
        }
        
        .icon-wrapper {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: rgba(30, 91, 168, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .icon-wrapper svg {
            width: 2rem;
            height: 2rem;
            color: var(--color-primary);
        }
        
        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--color-text);
        }
        
        p {
            font-size: 0.9375rem;
            color: var(--color-text-muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .alert-success {
            background: rgba(45, 122, 62, 0.1);
            color: var(--color-success);
            border: 1px solid rgba(45, 122, 62, 0.2);
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
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .divider {
            margin: 1.5rem 0;
            padding-top: 1.5rem;
            border-top: 1px solid var(--color-border);
        }
        
        .link {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-card">
            <div class="icon-wrapper">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            
            <h2>Verify your email address</h2>
            
            <p>
                Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent to <strong>{{ auth()->user()->email }}</strong>.
            </p>
            
            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Resend verification email
                </button>
            </form>
            
            <div class="divider">
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="link" style="background: none; border: none; cursor: pointer;">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

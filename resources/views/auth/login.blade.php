@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<div class="auth-wrap">
    <div class="auth-card card">
        <h1>Sign in to your account</h1>
        <p class="sub">Landlord access only.</p>
        <form method="post" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="checkbox" name="remember" style="width:auto;"> Remember me on this device
                </label>
            </div>
            <button class="btn" type="submit" style="width:100%;">Sign in</button>
        </form>

        <div class="divider">or</div>

        <a class="btn-google" href="{{ route('google.redirect') }}">
            <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.47 0 11.89-2.14 15.85-5.82l-7.73-6c-2.15 1.45-4.92 2.3-8.12 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Sign in with Google
        </a>

        <p class="muted" style="margin-top:18px;font-size:13px;text-align:center;">
            New landlord? <a href="{{ route('register') }}">Create an account</a>
        </p>
    </div>
</div>
@endsection

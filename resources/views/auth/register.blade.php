@extends('layouts.app')

@section('title', 'Create your account')

@section('content')
<div class="auth-wrap">
    <div class="auth-card card">
        <h1>Create your account</h1>
        <p class="sub">Start managing your rental portfolio.</p>
        <form method="post" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label for="name">Your name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>
            <button class="btn" type="submit" style="width:100%;">Create account</button>
        </form>

        <div class="divider">or</div>

        <a class="btn-google" href="{{ route('google.redirect') }}">
            <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.47 0 11.89-2.14 15.85-5.82l-7.73-6c-2.15 1.45-4.92 2.3-8.12 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Sign up with Google
        </a>

        <p class="muted" style="margin-top:18px;font-size:13px;text-align:center;">
            Already registered? <a href="{{ route('login') }}">Sign in</a>
        </p>
    </div>
</div>
@endsection

@extends('layouts.guest')
@section('title', 'Login')
@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-3"><label class="form-label">Email or Username</label><div class="input-group"><span class="input-group-text"><i class="bi bi-person"></i></span><input type="text" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" placeholder="Enter email or username" required autofocus></div>@error('login')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label class="form-label">Password</label><div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span><input type="password" name="password" class="form-control" placeholder="Enter password" required></div>@error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
    <div class="d-flex justify-content-between align-items-center mb-4"><div class="form-check"><input type="checkbox" name="remember" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}><label class="form-check-label small" for="remember">Remember me</label></div><a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot Password?</a></div>
    <button type="submit" class="btn btn-primary w-100 mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</button>
    <div class="text-center mb-3"><span class="text-muted small">or</span></div>
    <a href="{{ route('google.redirect') }}" class="btn btn-outline-dark w-100 mb-3"><i class="bi bi-google me-2"></i>Sign in with Google</a>
    <p class="text-center mb-0 small">Don't have an account? <a href="{{ route('register') }}">Sign Up</a></p>
</form>
@endsection

@extends('layouts.guest')
@section('title', 'Forgot Password')
@section('content')
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<p class="text-muted small mb-3">Enter your email and we'll send a password reset link.</p>
<form method="POST" action="{{ route('password.email') }}">@csrf
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>
    <p class="text-center small"><a href="{{ route('login') }}">Back to Login</a></p>
</form>
@endsection

@extends('layouts.guest')
@section('title', 'Register')
@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="mb-4"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
    <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
    <p class="text-center mb-0 small">Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
</form>
@endsection

@extends('layouts.guest')
@section('title', 'Two-Factor Authentication')
@section('content')
<p class="text-muted small mb-3">Enter the 6-digit code from your authenticator app.</p>
<form method="POST" action="{{ route('two-factor.verify') }}">@csrf
    <div class="mb-3"><label class="form-label">Verification Code</label><input type="text" name="code" class="form-control form-control-lg text-center @error('code') is-invalid @enderror" maxlength="6" placeholder="000000" required autofocus>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <button type="submit" class="btn btn-primary w-100">Verify</button>
</form>
@endsection

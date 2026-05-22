<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - {{ app_name() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh}.auth-card{max-width:440px;margin:auto}</style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    <div class="auth-card w-100 px-3">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <img src="{{ app_logo() }}" alt="Logo" height="48" class="mb-3" onerror="this.style.display='none'">
                    <h4 class="fw-bold">{{ app_name() }}</h4>
                </div>
                @yield('content')
            </div>
        </div>
        <p class="text-center text-white-50 mt-3 small">&copy; {{ date('Y') }} {{ app_name() }}</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

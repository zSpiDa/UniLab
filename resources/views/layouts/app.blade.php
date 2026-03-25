<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestionale Gruppo di Ricerca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="p-3">
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-3">
    <div class="container">
        <a href="/" class="navbar-brand mb-0"> <strong>UniLab</strong></a>

        @auth
        <div class="d-flex align-items-center gap-3">
            <a href="/" class="nav-link">Home</a>
            <a href="{{ route('projects.index') }}" class="nav-link">Progetti</a>
            <a href="{{ route('publications.index') }}" class="nav-link">Pubblicazioni</a>
            <a href="{{ route('tasks.index') }}" class="nav-link">Tasks</a>
            <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
            
            <span class="text-muted">|</span>
            <a href="{{ route('profile.edit') }}" class="nav-link">Ciao, {{ Auth::user()->name }} ({{ Auth::user()->role }})</a>
            
            <!-- Notification Bell -->
            <x-notification-bell></x-notification-bell>
            
            <a href="{{ route('logout') }}" class="btn btn-outline-secondary btn-sm"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </a>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>
        </div>
        @endauth
    </div>
</nav>

<main class="container py-3">
    @yield('content')
</main>

<footer class="mt-4 text-center text-muted">
    <small>&copy; 2025 Università di Bari</small>
</footer>
</body>
</html>

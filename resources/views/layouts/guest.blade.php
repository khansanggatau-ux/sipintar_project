<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIPINTAR')</title>
    
    <!-- Tambahan Font Google "Inter" agar modern -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
  <div class="login-wrap">
    <div class="login-hero">
      <div class="brand">
        <span class="dot"></span> SIPINTAR &middot; SMK Negeri 5 Surakarta
      </div>
      <div class="hero-text-wrapper">
        <h1>@yield('hero-title')</h1>
        <p>@yield('hero-sub')</p>
      </div>
      @hasSection('hero-stats')
        <div class="stats">@yield('hero-stats')</div>
      @endif
    </div>
    
    <div class="login-form-side">
      <div class="login-card">
        @yield('content')
      </div>
    </div>
  </div>
</body>
</html>
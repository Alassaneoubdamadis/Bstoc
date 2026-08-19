<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Back-office') — {{ platform_app_name() }}</title>
    @if(platform_favicon_url())
        <link rel="icon" href="{{ platform_favicon_url() }}">
    @endif
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"/>
    <style>
        :root {
            --bg: #EFF3F6;
            --panel: #FFFFFF;
            --line: #E9ECEF;
            --text: #212529;
            --muted: #6C757D;
            --accent: #6571FF;
            --accent-soft: #E0E3FF;
            --ok: #0AC074;
            --ok-bg: #CEF2E3;
            --warn: #FFB821;
            --warn-bg: #FFF1D3;
            --bad: #F62947;
            --bad-bg: #FFD6DC;
            --side: #FFFFFF;
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Poppins, Segoe UI, sans-serif; background:var(--bg); color:var(--text); }
        a { color:var(--accent); text-decoration:none; }
        h1 { font-size: 1.5rem; font-weight: 600; }
        h2 { font-size: 1.1rem; font-weight: 600; margin: 0 0 14px; }
        .app { display:flex; min-height:100vh; }
        .side { width:250px; background:var(--side); border-right:1px solid var(--line); padding:0 0 24px; }
        .brand { height:70px; display:flex; align-items:center; padding:0 1.4rem; font-weight:600; color:var(--accent); border-bottom:1px solid var(--line); letter-spacing:.02em; }
        .side-nav { padding: 16px 12px; }
        .side a.nav { display:block; padding:10px 12px; border-radius:8px; color:var(--muted); margin-bottom:4px; font-weight:500; font-size:14px; border-left: 4px solid transparent; }
        .side a.nav:hover, .side a.nav.active { background:var(--accent-soft); color:var(--accent); }
        .side a.nav.active { border-left-color: var(--accent); }
        .side .pos-link { display:block; margin: 16px 12px 0; text-align:center; }
        .main { flex:1; padding:28px 32px; }
        .top { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; gap:16px; }
        .cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:28px; }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:12px; padding:16px; box-shadow: 0 1px 4px rgba(15,34,58,.04); }
        .card b { display:block; font-size:28px; margin-top:6px; color:var(--accent); }
        .muted { color:var(--muted); font-size:13px; }
        table { width:100%; border-collapse:collapse; background:var(--panel); border-radius:12px; overflow:hidden; border:1px solid var(--line); }
        th, td { text-align:left; padding:12px 14px; border-bottom:1px solid var(--line); font-size:14px; }
        th { color:var(--muted); font-weight:600; background:#F8F9FA; }
        .badge { padding:3px 8px; border-radius:999px; font-size:12px; background:#F8F9FA; color:var(--muted); }
        .badge.ok { background:var(--ok-bg); color:#044D2E; }
        .badge.bad { background:var(--bad-bg); color:var(--bad); }
        .badge.warn { background:var(--warn-bg); color:#7A5608; }
        .btn { display:inline-block; background:var(--accent); color:#fff; font-weight:600; border:0; border-radius:8px; padding:8px 14px; cursor:pointer; font-family:inherit; font-size:14px; }
        .btn:hover { filter: brightness(.95); color:#fff; }
        .btn.ghost { background:transparent; color:var(--text); border:1px solid var(--line); }
        .btn.ghost:hover { background:#F8F9FA; color:var(--text); }
        .btn.bad { background:var(--bad); color:#fff; }
        input, select, textarea { width:100%; background:#fff; color:var(--text); border:1px solid var(--line); border-radius:8px; padding:10px 12px; font-family:inherit; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
        label { display:block; margin:12px 0 6px; color:var(--muted); font-size:13px; }
        .flash { background:var(--ok-bg); color:#044D2E; padding:10px 14px; border-radius:8px; margin-bottom:16px; }
        .errors { background:var(--bad-bg); color:#8A1024; padding:10px 14px; border-radius:8px; margin-bottom:16px; }
        .form { max-width:640px; background:var(--panel); padding:22px; border-radius:12px; border:1px solid var(--line); }
        .form.wide { max-width:960px; }
        .perm-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:6px 16px; margin:12px 0 8px; }
        .perm { display:flex; align-items:flex-start; gap:8px; color:var(--text); font-size:13px; margin:0; }
        .perm input { width:auto; margin-top:3px; }
        .app-footer { padding:16px 32px; color:var(--muted); font-size:12px; border-top:1px solid var(--line); background:#fff; }
        .row { display:flex; gap:12px; }
        .row > * { flex:1; }
        .actions { display:flex; gap:8px; }
        form.inline { display:inline; }
        .logout { margin: 24px 12px 0; }
        .logout .btn { width:100%; }
    </style>
</head>
<body>
<div class="app">
    <aside class="side">
        <div class="brand">
            @if(platform_logo_url())
                <img src="{{ platform_logo_url() }}" alt="{{ platform_app_name() }}" style="height:32px;width:auto;max-width:120px;object-fit:contain;margin-right:10px">
            @endif
            {{ strtoupper(platform_app_name()) }} · PLATEFORME
        </div>
        <nav class="side-nav">
            <a class="nav {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}" href="{{ route('platform.dashboard') }}">Tableau de bord</a>
            <a class="nav {{ request()->routeIs('platform.companies.*') ? 'active' : '' }}" href="{{ route('platform.companies.index') }}">Magasins</a>
            <a class="nav {{ request()->routeIs('platform.plans.*') ? 'active' : '' }}" href="{{ route('platform.plans.index') }}">Offres d’abonnement</a>
            <a class="nav {{ request()->routeIs('platform.branding.*') ? 'active' : '' }}" href="{{ route('platform.branding.edit') }}">Logo et nom</a>
        </nav>
        <a class="btn pos-link" href="/#/login" target="_blank" rel="noopener noreferrer">Ouvrir le POS</a>
        <form class="logout" method="POST" action="{{ route('platform.logout') }}">
            @csrf
            <button class="btn ghost" type="submit">Déconnexion</button>
        </form>
    </aside>
    <main class="main">
        @if(session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
</div>
<footer class="app-footer">Créé par Alassane Oubda — Tous droits réservés. Contact : oubdaalassane01@gmail.com · +225 0757613098</footer>
</body>
</html>

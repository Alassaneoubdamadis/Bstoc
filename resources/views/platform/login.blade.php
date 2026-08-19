<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion plateforme — {{ platform_app_name() }}</title>
    @if(platform_favicon_url())
        <link rel="icon" href="{{ platform_favicon_url() }}">
    @endif
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"/>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#EFF3F6; color:#212529; font-family:Poppins, Segoe UI, sans-serif; }
        .box { width:100%; max-width:400px; background:#fff; padding:32px; border-radius:16px; border:1px solid #E9ECEF; box-shadow: 0 8px 24px rgba(15,34,58,.06); }
        h1 { margin:0 0 8px; font-size:22px; color:#6571FF; font-weight:600; }
        p { color:#6C757D; margin:0 0 20px; font-size:14px; }
        label { display:block; margin:12px 0 6px; font-size:13px; color:#6C757D; }
        input { width:100%; padding:10px 12px; border-radius:8px; border:1px solid #E9ECEF; background:#fff; color:#212529; box-sizing:border-box; font-family:inherit; }
        input:focus { outline:none; border-color:#6571FF; box-shadow: 0 0 0 3px #E0E3FF; }
        button { margin-top:18px; width:100%; padding:12px; border:0; border-radius:8px; background:#6571FF; color:#fff; font-weight:600; cursor:pointer; font-family:inherit; }
        .err { background:#FFD6DC; color:#8A1024; padding:10px; border-radius:8px; margin-bottom:12px; font-size:14px; }
        .back { display:block; text-align:center; margin-top:16px; color:#6571FF; text-decoration:none; font-size:13px; }
    </style>
</head>
<body>
<div class="box">
    <h1>{{ platform_app_name() }}</h1>
    <p>Back-office propriétaire — gestion des magasins et abonnements</p>
    @if($errors->any())
        <div class="err">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('platform.login.submit') }}">
        @csrf
        <label>E-mail</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        <button type="submit">Se connecter</button>
    </form>
    <a class="back" href="/#/login">Aller à la caisse POS</a>
    <p style="margin-top:20px;font-size:11px;color:#6C757D;line-height:1.4;text-align:center">Créé par Alassane Oubda — Tous droits réservés.<br>Contact : oubdaalassane01@gmail.com · +225 0757613098</p>
</div>
</body>
</html>

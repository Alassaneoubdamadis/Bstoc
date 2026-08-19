@extends('platform.layout')

@section('title', 'Identité de l’application')

@section('content')
    <div class="top">
        <h1>Logo et nom de l’application</h1>
    </div>
    <p class="muted" style="margin-top:-12px;margin-bottom:20px">Ces éléments s’affichent dans le back-office, l’onglet du navigateur (favicon) et le POS si le magasin n’a pas encore chargé son propre logo.</p>
    <form class="form" method="POST" action="{{ route('platform.branding.update') }}" enctype="multipart/form-data">
        @csrf
        <label>Nom de l’application</label>
        <input type="text" name="app_name" value="{{ old('app_name', $appName) }}" required>
        <label>Logo (PNG, JPG, WEBP — max 2 Mo)</label>
        <input type="file" name="logo" accept="image/*">
        @if($logoUrl)
            <p class="muted" style="margin-top:12px">Aperçu actuel</p>
            <div style="display:flex;align-items:center;gap:16px;margin-top:8px">
                <img src="{{ $logoUrl }}" alt="Logo" style="max-height:64px;max-width:180px;object-fit:contain;background:#F8F9FA;padding:8px;border-radius:8px;border:1px solid #E9ECEF">
                <img src="{{ $faviconUrl }}" alt="Favicon" style="width:32px;height:32px;object-fit:contain;border:1px solid #E9ECEF;border-radius:6px">
            </div>
        @endif
        <div class="actions" style="margin-top:18px">
            <button class="btn" type="submit">Enregistrer</button>
        </div>
    </form>
@endsection

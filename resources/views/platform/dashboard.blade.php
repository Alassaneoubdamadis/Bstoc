@extends('platform.layout')
@section('title', 'Tableau de bord')
@section('content')
<div class="top">
    <div>
        <h1 style="margin:0">Pilotage plateforme</h1>
        <div class="muted">Magasins, essais 14 jours, plan Starter</div>
    </div>
    <a class="btn" href="{{ route('platform.companies.create') }}">Nouveau magasin</a>
</div>
<div class="cards">
    <div class="card"><span class="muted">Magasins</span><b>{{ $stats['total'] }}</b></div>
    <div class="card"><span class="muted">En essai</span><b>{{ $stats['trialing'] }}</b></div>
    <div class="card"><span class="muted">Actifs</span><b>{{ $stats['active'] }}</b></div>
    <div class="card"><span class="muted">Suspendus</span><b>{{ $stats['suspended'] }}</b></div>
    <div class="card"><span class="muted">Sans accès</span><b>{{ $stats['blocked'] }}</b></div>
    <div class="card"><span class="muted">Offres</span><b>{{ $stats['plans'] }}</b></div>
</div>
<h2>Derniers magasins</h2>
<table>
    <thead>
    <tr><th>Magasin</th><th>Offre</th><th>Statut</th><th>Essai jusqu’au</th></tr>
    </thead>
    <tbody>
    @forelse($companies->take(8) as $company)
        <tr>
            <td>{{ $company->name }}</td>
            <td>{{ $company->plan->name ?? '—' }}</td>
            <td><span class="badge {{ $company->hasAccess() ? 'ok' : 'bad' }}">{{ $company->accessLabel() }}</span></td>
            <td>{{ optional($company->trial_ends_at)->format('d/m/Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="4">Aucun magasin</td></tr>
    @endforelse
    </tbody>
</table>
@endsection

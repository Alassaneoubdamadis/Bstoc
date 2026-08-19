@extends('platform.layout')
@section('title', 'Magasins')
@section('content')
<div class="top">
    <h1 style="margin:0">Magasins clients</h1>
    <a class="btn" href="{{ route('platform.companies.create') }}">Créer un magasin</a>
</div>
<table>
    <thead>
    <tr>
        <th>Nom</th><th>Propriétaire</th><th>Offre</th><th>Accès</th><th></th>
    </tr>
    </thead>
    <tbody>
    @foreach($companies as $company)
        <tr>
            <td>{{ $company->name }}<div class="muted">{{ $company->email }}</div></td>
            <td>{{ optional($company->owner)->email }}</td>
            <td>{{ $company->plan->name ?? '—' }}</td>
            <td><span class="badge {{ $company->hasAccess() ? 'ok' : 'bad' }}">{{ $company->accessLabel() }}</span></td>
            <td class="actions">
                <a class="btn ghost" href="{{ route('platform.companies.edit', $company) }}">Modifier</a>
                <form class="inline" method="POST" action="{{ route('platform.companies.suspend', $company) }}">
                    @csrf
                    <button class="btn {{ $company->is_suspended ? 'ok' : 'bad' }}" type="submit">
                        {{ $company->is_suspended ? 'Réactiver' : 'Suspendre' }}
                    </button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div style="margin-top:16px">{{ $companies->links() }}</div>
@endsection

@extends('platform.layout')
@section('title', 'Offres')
@section('content')
<div class="top">
    <h1 style="margin:0">Offres d’abonnement</h1>
    <a class="btn" href="{{ route('platform.plans.create') }}">Nouvelle offre</a>
</div>
<table>
    <thead>
    <tr><th>Nom</th><th>Prix</th><th>Essai</th><th>Active</th><th></th></tr>
    </thead>
    <tbody>
    @foreach($plans as $plan)
        <tr>
            <td>{{ $plan->name }}<div class="muted">{{ $plan->slug }}</div></td>
            <td>{{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }} / {{ $plan->interval === 'year' ? 'an' : 'mois' }}</td>
            <td>{{ $plan->trial_days }} jours</td>
            <td>{{ $plan->is_active ? 'Oui' : 'Non' }}</td>
            <td><a class="btn ghost" href="{{ route('platform.plans.edit', $plan) }}">Modifier</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection

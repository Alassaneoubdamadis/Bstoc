@extends('platform.layout')
@section('title', 'Nouveau magasin')
@section('content')
<h1>Nouveau magasin</h1>
<p class="muted">Le propriétaire reçoit un essai de 14 jours sur le plan choisi (Starter par défaut).</p>
@if($errors->any())
    <div class="errors"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<form class="form wide" method="POST" action="{{ route('platform.companies.store') }}">
    @csrf
    <h3>Magasin</h3>
    <label>Nom du magasin</label>
    <input name="name" value="{{ old('name') }}" required>
    <div class="row">
        <div><label>E-mail magasin</label><input type="email" name="email" value="{{ old('email') }}" required></div>
        <div><label>Téléphone</label><input name="phone" value="{{ old('phone') }}"></div>
    </div>
    <div class="row">
        <div><label>Ville</label><input name="city" value="{{ old('city', 'Abidjan') }}"></div>
        <div><label>Pays</label><input name="country" value="{{ old('country', 'Côte d\'Ivoire') }}"></div>
    </div>
    <label>Offre</label>
    <select name="subscription_plan_id" required>
        @foreach($plans as $plan)
            <option value="{{ $plan->id }}" @selected($plan->slug === 'starter')>{{ $plan->name }} — essai {{ $plan->trial_days }} j</option>
        @endforeach
    </select>
    <h3>Propriétaire (connexion POS)</h3>
    <div class="row">
        <div><label>Prénom</label><input name="owner_first_name" value="{{ old('owner_first_name') }}" required></div>
        <div><label>Nom</label><input name="owner_last_name" value="{{ old('owner_last_name') }}" required></div>
    </div>
    <label>E-mail de connexion</label>
    <input type="email" name="owner_email" value="{{ old('owner_email') }}" required>
    <label>Mot de passe</label>
    <input type="text" name="owner_password" value="{{ old('owner_password', '123456') }}" required>
    @include('platform.companies._permissions')
    <button class="btn" style="margin-top:18px" type="submit">Créer et lancer l’essai</button>
</form>
@endsection

@extends('platform.layout')
@section('title', 'Modifier magasin')
@section('content')
<h1>{{ $company->name }}</h1>
@if($errors->any())
    <div class="errors"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<form class="form wide" method="POST" action="{{ route('platform.companies.update', $company) }}">
    @csrf
    @method('PUT')
    <label>Nom</label>
    <input name="name" value="{{ old('name', $company->name) }}" required>
    <div class="row">
        <div><label>E-mail</label><input type="email" name="email" value="{{ old('email', $company->email) }}"></div>
        <div><label>Téléphone</label><input name="phone" value="{{ old('phone', $company->phone) }}"></div>
    </div>
    <div class="row">
        <div><label>Ville</label><input name="city" value="{{ old('city', $company->city) }}"></div>
        <div><label>Pays</label><input name="country" value="{{ old('country', $company->country) }}"></div>
    </div>
    <label>Offre</label>
    <select name="subscription_plan_id">
        @foreach($plans as $plan)
            <option value="{{ $plan->id }}" @selected($company->subscription_plan_id == $plan->id)>{{ $plan->name }} ({{ $plan->price }} {{ $plan->currency }}/{{ $plan->interval }})</option>
        @endforeach
    </select>
    <label>Statut</label>
    <select name="status">
        @foreach(['trialing'=>'Essai','active'=>'Actif','past_due'=>'Impayé','canceled'=>'Résilié','expired'=>'Expiré'] as $k=>$label)
            <option value="{{ $k }}" @selected($company->status === $k)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="row">
        <div><label>Fin d’essai</label><input type="datetime-local" name="trial_ends_at" value="{{ optional($company->trial_ends_at)->format('Y-m-d\TH:i') }}"></div>
        <div><label>Fin d’abonnement</label><input type="datetime-local" name="subscription_ends_at" value="{{ optional($company->subscription_ends_at)->format('Y-m-d\TH:i') }}"></div>
    </div>
    <label><input type="checkbox" name="is_suspended" value="1" @checked($company->is_suspended)> Suspendre l’accès caisse</label>
    <label>Notes internes</label>
    <textarea name="notes" rows="3">{{ old('notes', $company->notes) }}</textarea>
    @include('platform.companies._permissions')
    <button class="btn" style="margin-top:18px" type="submit">Enregistrer</button>
</form>
@endsection

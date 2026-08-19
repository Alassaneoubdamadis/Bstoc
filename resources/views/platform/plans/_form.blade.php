@if($errors->any())
    <div class="errors"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<form class="form" method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif
    <label>Nom</label>
    <input name="name" value="{{ old('name', $plan->name ?? '') }}" required>
    <label>Slug (laisser vide pour générer)</label>
    <input name="slug" value="{{ old('slug', $plan->slug ?? '') }}">
    <div class="row">
        <div><label>Prix</label><input type="number" step="1" name="price" value="{{ old('price', $plan->price ?? 0) }}" required></div>
        <div><label>Devise</label><input name="currency" value="{{ old('currency', $plan->currency ?? 'XOF') }}"></div>
    </div>
    <div class="row">
        <div>
            <label>Période</label>
            <select name="interval">
                <option value="month" @selected(old('interval', $plan->interval ?? 'month')==='month')>Mensuel</option>
                <option value="year" @selected(old('interval', $plan->interval ?? '')==='year')>Annuel</option>
            </select>
        </div>
        <div><label>Jours d’essai</label><input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days ?? 14) }}"></div>
        <div><label>Ordre</label><input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}"></div>
    </div>
    <label>Description</label>
    <textarea name="description" rows="2">{{ old('description', $plan->description ?? '') }}</textarea>
    <label>Fonctionnalités (une par ligne)</label>
    <textarea name="features_text" rows="5">{{ old('features_text', implode("\n", $plan->features ?? [])) }}</textarea>
    <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? true))> Offre active</label>
    <button class="btn" style="margin-top:18px" type="submit">Enregistrer l’offre</button>
</form>

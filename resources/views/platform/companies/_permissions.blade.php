@php
    $selected = old('permissions', $selectedPermissions ?? null);
    if ($selected === null) {
        $selected = $allPermissionNames;
    }
@endphp
<h3>Droits du magasin (POS)</h3>
<p class="muted">Coche ce que le client a le droit d’utiliser. Décoche par exemple « Réglages » ou « Utilisateurs » s’il ne doit pas le faire.</p>
<label><input type="checkbox" id="perm-all" onclick="document.querySelectorAll('.perm-item').forEach(function(c){c.checked=this.checked}.bind(this))"> Tout cocher / décocher</label>
<div class="perm-grid">
    @foreach($permissionOptions as $name => $label)
        <label class="perm">
            <input class="perm-item" type="checkbox" name="permissions[]" value="{{ $name }}" @checked(in_array($name, $selected, true))>
            {{ $label }}
        </label>
    @endforeach
</div>

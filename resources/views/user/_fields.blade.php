<x-ui.form-section title="Profil dan akses" icon="person">
    <div class="md3-form-grid">
        <x-ui.text-field label="Username" id="username" name="username" maxlength="45" autocomplete="username"
            autocapitalize="none" spellcheck="false" leading-icon="person" :value="$user->username ?? null" required />
        <x-ui.select label="Role" id="idrole" name="idrole" required>
            @if ($user === null)
                <option value="">Pilih role</option>
            @endif
            @foreach ($daftarRole as $role)
                <option value="{{ $role->idrole }}" @selected(old('idrole', $user->idrole ?? null) == $role->idrole)>
                    {{ $role->nama_role }}
                </option>
            @endforeach
        </x-ui.select>
    </div>
</x-ui.form-section>

<x-ui.form-section title="Kata sandi" icon="lock"
    :description="$user === null ? 'Gunakan minimal 8 karakter.' : 'Kosongkan kedua kolom untuk mempertahankan kata sandi saat ini. Jika diganti, gunakan minimal 8 karakter.'">
    <div class="md3-form-grid">
        <x-ui.password-field id="password" name="password" :label="$user === null ? 'Kata sandi' : 'Kata sandi baru'"
            autocomplete="new-password" :required="$user === null" />
        <x-ui.password-field id="password_confirmation" name="password_confirmation" label="Konfirmasi kata sandi"
            autocomplete="new-password" :required="$user === null" />
    </div>
</x-ui.form-section>

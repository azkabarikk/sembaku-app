@props(['id', 'label' => 'Kata sandi'])

<x-ui.text-field :$id :$label type="password" leading-icon="lock" {{ $attributes }}>
    <x-slot:trailing>
        <x-ui.icon-button label="Tampilkan {{ mb_strtolower($label) }}" aria-controls="{{ $id }}"
            data-md3-password data-password-label="{{ mb_strtolower($label) }}" hidden>
            <x-ui.icon name="eye" data-password-icon="show" />
            <x-ui.icon name="eye-off" data-password-icon="hide" hidden />
        </x-ui.icon-button>
    </x-slot:trailing>
</x-ui.text-field>

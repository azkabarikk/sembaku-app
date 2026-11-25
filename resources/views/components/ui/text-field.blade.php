@props(['useOld' => true, 'leadingIcon' => null])

<x-ui.field-control kind="input" :use-old="$useOld" :leading-icon="$leadingIcon" {{ $attributes }}>
    @isset($trailing)
        <x-slot:trailing>{{ $trailing }}</x-slot:trailing>
    @endisset
    {{ $slot }}
</x-ui.field-control>

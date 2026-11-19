@props(['useOld' => true])

<x-ui.field-control kind="input" :use-old="$useOld" {{ $attributes }}>{{ $slot }}</x-ui.field-control>

@props(['level' => 0])

<div {{ $attributes->class(['md3-surface', 'md3-elevation-'.max(0, min(5, (int) $level))]) }}>
    {{ $slot }}
</div>

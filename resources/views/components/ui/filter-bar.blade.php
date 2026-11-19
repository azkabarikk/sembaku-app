<div {{ $attributes->class(['md3-filter-bar']) }}>
    {{ $slot }}
    @isset($actions)
        <div class="md3-cluster md3-filter-bar__actions">{{ $actions }}</div>
    @endisset
</div>

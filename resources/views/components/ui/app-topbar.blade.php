@props(['title' => null])

<header {{ $attributes->class(['md3-topbar']) }}>
    <div class="md3-cluster">
        @isset($leading)
            {{ $leading }}
        @endisset
        @if ($title !== null)
            <span class="md3-type-title-large">{{ $title }}</span>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="md3-cluster">{{ $actions }}</div>
    @endisset
</header>

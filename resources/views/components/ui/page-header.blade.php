@props(['title', 'subtitle' => null, 'heading' => 'h1', 'icon' => null])

@php($heading = in_array($heading, ['h1', 'h2', 'h3'], true) ? $heading : 'h1')

<header {{ $attributes->class(['md3-page-header', 'md3-page-header--illustrated' => $icon !== null]) }}>
    <div class="md3-page-header__lead">
        @if ($icon !== null)
            <span class="md3-page-header__icon"><x-ui.icon :name="$icon" /></span>
        @endif
        <div class="md3-page-header__copy">
            <{{ $heading }} class="md3-page-header__title">{{ $title }}</{{ $heading }}>
            @if (filled($subtitle))
                <p class="md3-page-header__subtitle">{{ $subtitle }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
    @isset($actions)
        <div class="md3-cluster">{{ $actions }}</div>
    @endisset
</header>

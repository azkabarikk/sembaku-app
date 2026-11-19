@props(['title', 'subtitle' => null, 'heading' => 'h1'])

@php($heading = in_array($heading, ['h1', 'h2', 'h3'], true) ? $heading : 'h1')

<header {{ $attributes->class(['md3-page-header']) }}>
    <div class="md3-page-header__copy">
        <{{ $heading }} class="md3-page-header__title">{{ $title }}</{{ $heading }}>
        @if ($subtitle !== null)
            <p class="md3-page-header__subtitle">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="md3-cluster">{{ $actions }}</div>
    @endisset
</header>

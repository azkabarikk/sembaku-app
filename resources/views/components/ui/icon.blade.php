@props(['name'])

<svg {{ $attributes->class(['md3-icon'])->merge(['aria-hidden' => 'true', 'focusable' => 'false']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        @case('add')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('close')
            <path d="m6 6 12 12M18 6 6 18"/>
            @break
        @case('search')
            <circle cx="10.5" cy="10.5" r="5.5"/><path d="m15 15 4 4"/>
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break
        @case('person')
            <circle cx="12" cy="8" r="3.25"/><path d="M5 20v-1a7 7 0 0 1 14 0v1"/>
            @break
        @case('package')
            <rect x="4" y="6" width="16" height="14" rx="2"/><path d="M4 10h16M9 6v4m6-4v4m-4 4h2"/>
            @break
        @case('check')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('arrow-right')
            <path d="M5 12h14m-6-6 6 6-6 6"/>
            @break
        @case('warning')
            <path d="M12 9v4m0 3h.01M10.3 4.6 3.6 17a2 2 0 0 0 1.8 3h13.2a2 2 0 0 0 1.8-3L13.7 4.6a2 2 0 0 0-3.4 0Z"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/>
            @break
    @endswitch
</svg>

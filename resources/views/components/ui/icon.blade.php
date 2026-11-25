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
        @case('scale')
            <path d="M12 3v18M5 6h14M7 6l-3 7h6L7 6Zm10 0-3 7h6l-3-7ZM8 21h8"/>
            @break
        @case('store')
            <path d="M4 10v10h16V10M3 10l2-6h14l2 6M3 10a3 3 0 0 0 5 2 3 3 0 0 0 4 0 3 3 0 0 0 4 0 3 3 0 0 0 5-2M9 20v-5h6v5"/>
            @break
        @case('shield')
            <path d="M12 3 5 6v5c0 4.8 2.9 8.1 7 10 4.1-1.9 7-5.2 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>
            @break
        @case('percent')
            <circle cx="7" cy="7" r="2.25"/><circle cx="17" cy="17" r="2.25"/><path d="m18.5 5.5-13 13"/>
            @break
        @case('shopping-basket')
            <path d="m6.5 9 4-5.5m7 5.5-4-5.5"/>
            <rect x="2" y="9" width="20" height="3.5" rx="1.5"/>
            <path d="m4 12.5 1.6 6.7A2 2 0 0 0 7.5 21h9a2 2 0 0 0 1.9-1.8l1.6-6.7M10 15.5v2.5m4-2.5v2.5"/>
            @break
        @case('shopping-cart')
            <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6"/><circle cx="9.5" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
            @break
        @case('package-check')
            <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4.5 7.5 7.5 4 7.5-4M12 12v9m-3.5-7.5 2 2 4-4"/>
            @break
        @case('receipt')
            <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6M9 16h4"/>
            @break
        @case('rotate-left')
            <path d="M4 8v5h5M5 13a7 7 0 1 0 2-6"/>
            @break
        @case('clipboard-list')
            <rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4.5V3h6v1.5M9 10h.01M12 10h4M9 14h.01M12 14h4M9 18h.01M12 18h4"/>
            @break
        @case('warehouse')
            <path d="m3 10 9-6 9 6v11H3V10Z"/><path d="M7 21v-7h10v7M7 17h10M9 10h.01M12 10h.01M15 10h.01"/>
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

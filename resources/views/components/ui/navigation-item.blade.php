@props(['href', 'active' => false, 'icon' => null])

<a {{ $attributes->class(['md3-navigation-item', 'md3-action', 'md3-navigation-item--active' => $active])->merge([
    'href' => $href, 'aria-current' => $active ? 'page' : null,
]) }}>
    @if (isset($leading) && $leading->isNotEmpty())
        {{ $leading }}
    @elseif ($icon !== null)
        <x-ui.icon :name="$icon" />
    @endif
    <span>{{ $slot }}</span>
</a>

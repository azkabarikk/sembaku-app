@props(['label' => 'Navigasi utama', 'id' => null])

@php($id ??= 'md3-nav-'.\Illuminate\Support\Str::uuid())

<aside {{ $attributes->class(['md3-sidebar']) }}>
    @isset($brand)
        <div class="md3-sidebar__brand">{{ $brand }}</div>
    @endisset
    <x-ui.icon-button label="{{ $label }}" icon="menu" class="md3-sidebar__toggle"
        aria-controls="{{ $id }}" aria-expanded="true" data-md3-disclosure hidden />
    <div id="{{ $id }}" class="md3-sidebar__body">
        <nav aria-label="{{ $label }}" class="md3-sidebar__nav">{{ $slot }}</nav>
        @isset($footer)
            <div class="md3-sidebar__footer">{{ $footer }}</div>
        @endisset
    </div>
</aside>

@props(['orientation' => 'horizontal'])

@if ($orientation === 'vertical')
    <span {{ $attributes->class(['md3-divider md3-divider--vertical'])->merge(['role' => 'separator', 'aria-orientation' => 'vertical']) }}></span>
@else
    <hr {{ $attributes->class(['md3-divider']) }}>
@endif

@props(['username', 'role' => null])

<div {{ $attributes->class(['md3-user-profile']) }}>
    <span class="md3-user-profile__avatar">
        @if (isset($avatar) && $avatar->isNotEmpty())
            {{ $avatar }}
        @else
            <x-ui.icon name="person" />
        @endif
    </span>
    <div class="md3-user-profile__details">
        <span class="md3-type-title-small">{{ $username }}</span>
        @if ($role !== null)
            <span class="md3-type-body-small md3-muted">{{ $role }}</span>
        @endif
    </div>
</div>

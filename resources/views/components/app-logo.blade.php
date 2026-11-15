@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="__('Osun Marketing Directory')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-white">
            <img src="{{ asset('images/osun-marketing-directory-icon.svg') }}" alt="" class="size-8">
        </x-slot>
    </flux:sidebar.brand>
@else
    <a {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center']) }}>
        <img
            src="{{ asset('images/osun-marketing-directory-logo.svg') }}"
            alt="Osun Marketing Directory. Discover. Connect. Grow."
            class="h-10 w-auto max-w-[min(60vw,18rem)] rounded-lg bg-white p-1"
        >
    </a>
@endif

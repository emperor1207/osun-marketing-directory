@props(['title' => null])

<x-layouts::auth.split :title="$title">
    {{ $slot }}
</x-layouts::auth.split>

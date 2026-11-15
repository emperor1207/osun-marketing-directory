@if ($imageUrl)
    <img
        src="{{ $imageUrl }}"
        alt="{{ $product->name }}"
        width="720"
        height="480"
        loading="lazy"
        decoding="async"
        {{ $attributes->merge(['class' => $height.' w-full object-cover']) }}
    >
@else
    <div
        role="img"
        aria-label="{{ $product->name }} image unavailable"
        {{ $attributes->merge(['class' => $height.' flex items-center justify-center bg-gradient-to-br from-emerald-950 via-emerald-800 to-teal-700 text-sm font-medium text-emerald-100']) }}
    >
        Image unavailable
    </div>
@endif
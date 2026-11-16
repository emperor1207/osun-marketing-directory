<section class="flex flex-col gap-5" aria-labelledby="business-products-heading">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">What they offer</p>
        <h2 id="business-products-heading" class="mt-1 text-2xl font-bold text-zinc-950 dark:text-white">Products &amp; Services</h2>
    </div>

    @if ($products->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="font-semibold text-zinc-950 dark:text-white">No products or services listed yet</h3>
            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">Check back later for updates from this business.</p>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    @php($slides = $product->galleryUrls())
                    @if (count($slides) > 1)
                        <div x-data="{ active: 0, slides: @js($slides) }" class="bg-zinc-100 dark:bg-zinc-950">
                            <img :src="slides[active]" alt="{{ $product->name }}" width="720" height="480" loading="lazy" decoding="async" class="h-44 w-full object-cover">
                            <div class="flex gap-2 overflow-x-auto p-2">
                                <template x-for="(slide, index) in slides" :key="index">
                                    <button type="button" x-on:click="active = index" x-bind:class="active === index ? 'ring-2 ring-emerald-600' : 'opacity-70'" class="shrink-0 overflow-hidden rounded-md" x-bind:aria-label="'Show photo ' + (index + 1)">
                                        <img :src="slide" alt="" class="h-12 w-16 object-cover">
                                    </button>
                                </template>
                            </div>
                        </div>
                    @else
                        <x-product-image :product="$product" height="h-44" />
                    @endif
                    <div class="flex flex-1 flex-col gap-3 p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold capitalize text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ $product->item_type }}</span>
                            <span @class([
                                'rounded-full px-3 py-1 text-xs font-semibold',
                                'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' => $product->is_available,
                                'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => ! $product->is_available,
                            ])>{{ $product->is_available ? 'Available' : 'Unavailable' }}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $product->name }}</h3>
                        @if ($product->description)
                            <p class="whitespace-pre-line text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $product->description }}</p>
                        @endif
                        <p class="mt-auto pt-2 text-lg font-bold text-emerald-800 dark:text-emerald-300">₦{{ number_format((float) $product->price, 2) }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

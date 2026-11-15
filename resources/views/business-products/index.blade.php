<x-layouts::app :title="__('Products & Services')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex flex-col gap-3">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Business owner</p>
                <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white sm:text-4xl">Products &amp; Services</h1>
                <p class="max-w-2xl leading-7 text-zinc-600 dark:text-zinc-300">Manage what {{ $businessProfile->business_name }} offers to customers.</p>
            </div>
            <a href="{{ route('business-products.create') }}" class="inline-flex min-h-11 w-fit items-center justify-center rounded-xl bg-emerald-800 px-5 font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                Add an item
            </a>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($products->isEmpty())
            <section class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-emerald-100 text-lg font-bold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" aria-hidden="true">+</span>
                <h2 class="mt-4 text-lg font-semibold text-zinc-950 dark:text-white">No products or services yet</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-zinc-600 dark:text-zinc-400">Add an item to help customers understand what your business offers.</p>
                <a href="{{ route('business-products.create') }}" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-800 px-4 text-sm font-semibold text-white transition hover:bg-emerald-900">
                    Add your first item
                </a>
            </section>
        @else
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" aria-label="Your products and services">
                @foreach ($products as $product)
                    <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <x-product-image :product="$product" height="h-48" />
                        <div class="flex flex-1 flex-col gap-4 p-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold capitalize text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ $product->item_type }}</span>
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' => $product->is_available,
                                    'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => ! $product->is_available,
                                ])>{{ $product->is_available ? 'Available' : 'Unavailable' }}</span>
                            </div>
                            <div class="flex flex-1 flex-col gap-2">
                                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $product->name }}</h2>
                                @if ($product->description)
                                    <p class="whitespace-pre-line text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $product->description }}</p>
                                @endif
                                <p class="mt-auto pt-2 text-lg font-bold text-emerald-800 dark:text-emerald-300">₦{{ number_format((float) $product->price, 2) }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                                <a href="{{ route('business-products.edit', $product) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-zinc-300 px-4 text-sm font-semibold text-zinc-800 transition hover:border-emerald-500 hover:bg-emerald-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">Edit</a>
                                <form method="POST" action="{{ route('business-products.destroy', $product) }}" onsubmit="return confirm('Delete this product or service?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-red-200 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950/40">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </main>
</x-layouts::app>

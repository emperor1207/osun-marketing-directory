<x-layouts::app :title="__('Edit product or service')">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <a href="{{ route('business-products.index') }}" class="w-fit text-sm font-semibold text-emerald-800 hover:underline dark:text-emerald-300">Products &amp; Services</a>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Edit {{ $product->name }}</h1>
        </header>

        @include('business-products._form')
    </main>
</x-layouts::app>

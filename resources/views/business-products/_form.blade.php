<form
    method="POST"
    action="{{ $product ? route('business-products.update', $product) : route('business-products.store') }}"
    enctype="multipart/form-data"
    class="flex flex-col gap-6"
>
    @csrf
    @if ($product)
        @method('PUT')
    @endif

    <section class="grid gap-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
        <div>
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Item details</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Add clear information customers can use to choose your offering.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Name</label>
                <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name', $product?->name) }}" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="item_type" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Item type</label>
                <select id="item_type" name="item_type" required class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="product" @selected(old('item_type', $product?->item_type ?? 'product') === 'product')>Product</option>
                    <option value="service" @selected(old('item_type', $product?->item_type) === 'service')>Service</option>
                </select>
                @error('item_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="price" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Price (NGN)</label>
                <input id="price" name="price" type="number" min="0" max="9999999999.99" step="0.01" required value="{{ old('price', $product?->price) }}" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Description <span class="font-normal text-zinc-500">(optional)</span></label>
                <textarea id="description" name="description" rows="4" maxlength="5000" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('description', $product?->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="image" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Image <span class="font-normal text-zinc-500">(optional, JPG/PNG/WebP up to 4 MB)</span></label>
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-sm text-zinc-700 file:me-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:font-medium file:text-zinc-800 dark:text-zinc-300 dark:file:bg-zinc-800 dark:file:text-zinc-100">
                @if ($product)
                    <x-product-image :product="$product" height="h-40" class="mt-3 rounded-xl border border-zinc-200 dark:border-zinc-700 sm:w-72" />
                @endif
                @if ($product?->image)
                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Choose a new image to replace the current one.</p>
                @endif
                @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <input type="hidden" name="is_available" value="0">
                <label class="flex items-center gap-2 text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product?->is_available ?? true)) class="size-4 rounded border-zinc-300 text-emerald-700 focus:ring-emerald-700">
                    Available to customers
                </label>
                @error('is_available') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('business-products.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 px-5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Cancel</a>
        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
            {{ $product ? 'Save changes' : 'Add item' }}
        </button>
    </div>
</form>

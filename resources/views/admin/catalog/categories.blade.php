<x-layouts::app :title="__('Manage categories')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <a href="{{ route('admin.dashboard') }}" class="w-fit text-sm font-semibold text-green-800 hover:underline dark:text-green-300">Administration</a>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">Business categories</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Add or update categories used to classify directory listings. Categories can be deactivated without deleting existing records.</p>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/40 dark:text-green-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6" aria-labelledby="create-category-heading">
            <h2 id="create-category-heading" class="text-lg font-semibold text-zinc-900 dark:text-white">Add a category</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label for="new-category-name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Category name</label>
                    <input id="new-category-name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-category-description" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Description (optional)</label>
                    <input id="new-category-description" name="description" value="{{ old('description') }}" maxlength="2000" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300 md:col-span-2">
                    <input type="checkbox" name="is_active" value="1" checked class="size-4 rounded border-zinc-300 text-green-700 focus:ring-green-700">
                    Active and available for new listings
                </label>
                <button type="submit" class="inline-flex w-fit rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Create category</button>
            </form>
        </section>

        <section class="flex flex-col gap-4" aria-label="Existing categories">
            @forelse ($categories as $category)
                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 md:grid-cols-[1fr_1.5fr_auto_auto] md:items-end">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="category-name-{{ $category->id }}" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Name</label>
                        <input id="category-name-{{ $category->id }}" name="name" value="{{ $category->name }}" required maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    </div>
                    <div>
                        <label for="category-description-{{ $category->id }}" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Description</label>
                        <input id="category-description-{{ $category->id }}" name="description" value="{{ $category->description }}" maxlength="2000" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" name="is_active" value="1" @checked($category->is_active) class="size-4 rounded border-zinc-300 text-green-700 focus:ring-green-700">
                        Active
                    </label>
                    <button type="submit" class="inline-flex w-fit rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Save</button>
                </form>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="font-medium text-zinc-900 dark:text-white">No categories have been created.</p>
                </div>
            @endforelse
            {{ $categories->links() }}
        </section>
    </main>
</x-layouts::app>

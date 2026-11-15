<x-layouts::app :title="__('Manage locations')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <a href="{{ route('admin.dashboard') }}" class="w-fit text-sm font-semibold text-green-800 hover:underline dark:text-green-300">Administration</a>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">Local government areas and towns</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Manage directory locations. Records are deactivated rather than deleted to preserve existing business profiles.</p>
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

        <section class="grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('admin.local-government-areas.store') }}" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                @csrf
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Add local government area</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Enter a unique LGA name and optional headquarters.</p>
                </div>
                <div>
                    <label for="new-lga-name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Name</label>
                    <input id="new-lga-name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-lga-headquarters" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Headquarters (optional)</label>
                    <input id="new-lga-headquarters" name="headquarters" value="{{ old('headquarters') }}" maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @error('headquarters') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" name="is_active" value="1" checked class="size-4 rounded border-zinc-300 text-green-700 focus:ring-green-700">
                    Active for new listings
                </label>
                <button type="submit" class="inline-flex w-fit rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Create LGA</button>
            </form>

            <form method="POST" action="{{ route('admin.towns.store') }}" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                @csrf
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Add town</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Select the LGA this town belongs to.</p>
                </div>
                <div>
                    <label for="new-town-name" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Town name</label>
                    <input id="new-town-name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new-town-lga" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Local government area</label>
                    <select id="new-town-lga" name="local_government_area_id" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <option value="">Select an LGA</option>
                        @foreach ($allLocalGovernmentAreas as $localGovernmentArea)
                            <option value="{{ $localGovernmentArea->id }}" @selected(old('local_government_area_id') == $localGovernmentArea->id)>{{ $localGovernmentArea->name }}</option>
                        @endforeach
                    </select>
                    @error('local_government_area_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" name="is_active" value="1" checked class="size-4 rounded border-zinc-300 text-green-700 focus:ring-green-700">
                    Active for new listings
                </label>
                <button type="submit" class="inline-flex w-fit rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Create town</button>
            </form>
        </section>

        <section class="flex flex-col gap-4" aria-labelledby="lga-list-heading">
            <h2 id="lga-list-heading" class="text-xl font-semibold text-zinc-900 dark:text-white">Local government areas</h2>
            @forelse ($localGovernmentAreas as $localGovernmentArea)
                <form method="POST" action="{{ route('admin.local-government-areas.update', $localGovernmentArea) }}" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 md:grid-cols-[1fr_1fr_auto_auto] md:items-end">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="lga-name-{{ $localGovernmentArea->id }}" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Name</label>
                        <input id="lga-name-{{ $localGovernmentArea->id }}" name="name" value="{{ $localGovernmentArea->name }}" required maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    </div>
                    <div>
                        <label for="lga-headquarters-{{ $localGovernmentArea->id }}" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Headquarters</label>
                        <input id="lga-headquarters-{{ $localGovernmentArea->id }}" name="headquarters" value="{{ $localGovernmentArea->headquarters }}" maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" name="is_active" value="1" @checked($localGovernmentArea->is_active) class="size-4 rounded border-zinc-300 text-green-700 focus:ring-green-700">
                        Active
                    </label>
                    <button type="submit" class="inline-flex w-fit rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Save</button>
                </form>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="font-medium text-zinc-900 dark:text-white">No local government areas have been added.</p>
                </div>
            @endforelse
            {{ $localGovernmentAreas->links() }}
        </section>

        <section class="flex flex-col gap-4" aria-labelledby="town-list-heading">
            <h2 id="town-list-heading" class="text-xl font-semibold text-zinc-900 dark:text-white">Towns</h2>
            @forelse ($towns as $town)
                <form method="POST" action="{{ route('admin.towns.update', $town) }}" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 md:grid-cols-[1fr_1fr_auto_auto] md:items-end">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="town-name-{{ $town->id }}" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Town</label>
                        <input id="town-name-{{ $town->id }}" name="name" value="{{ $town->name }}" required maxlength="255" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    </div>
                    <div>
                        <label for="town-lga-{{ $town->id }}" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Local government area</label>
                        <select id="town-lga-{{ $town->id }}" name="local_government_area_id" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-green-700 focus:ring-2 focus:ring-green-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            @foreach ($allLocalGovernmentAreas as $localGovernmentArea)
                                <option value="{{ $localGovernmentArea->id }}" @selected($town->local_government_area_id === $localGovernmentArea->id)>{{ $localGovernmentArea->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" name="is_active" value="1" @checked($town->is_active) class="size-4 rounded border-zinc-300 text-green-700 focus:ring-green-700">
                        Active
                    </label>
                    <button type="submit" class="inline-flex w-fit rounded-lg border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Save</button>
                </form>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="font-medium text-zinc-900 dark:text-white">No towns have been added.</p>
                </div>
            @endforelse
            {{ $towns->links() }}
        </section>
    </main>
</x-layouts::app>

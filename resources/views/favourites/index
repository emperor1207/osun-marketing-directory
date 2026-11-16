<x-layouts::app :title="__('Saved businesses')">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-2">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Your shortlist</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Saved businesses</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Businesses you saved so you can find them again quickly.</p>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif

        @if ($businessProfiles->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">You have not saved any business yet</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Tap the heart on a business to save it here.</p>
                <a href="{{ route('directory.index') }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 text-sm font-semibold text-white hover:bg-emerald-900">Browse the directory</a>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($businessProfiles as $businessProfile)
                    <article class="flex min-w-0 flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-4">
                            @if ($businessProfile->logo_url)
                                <img src="{{ $businessProfile->logo_url }}" alt="" width="56" height="56" loading="lazy" class="size-14 shrink-0 rounded-2xl border border-zinc-200 object-cover dark:border-zinc-700">
                            @endif
                            <div class="min-w-0">
                                <h2 class="truncate text-lg font-semibold text-zinc-950 dark:text-white">{{ $businessProfile->business_name }}</h2>
                                <p class="truncate text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ $businessProfile->category->name }}</p>
                                <p class="truncate text-sm text-zinc-600 dark:text-zinc-400">{{ $businessProfile->town->name }}, {{ $businessProfile->town->localGovernmentArea->name }}</p>
                            </div>
                        </div>

                        <p class="text-sm font-medium text-amber-700 dark:text-amber-300">
                            @if ($businessProfile->visible_reviews_count > 0)
                                <span aria-hidden="true">★</span> {{ number_format((float) $businessProfile->visible_reviews_avg_rating, 1) }} / 5 · {{ number_format($businessProfile->visible_reviews_count) }} {{ \Illuminate\Support\Str::plural('review', $businessProfile->visible_reviews_count) }}
                            @else
                                No ratings yet
                            @endif
                        </p>

                        <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <a href="{{ route('directory.show', $businessProfile) }}" class="inline-flex min-h-10 items-center text-sm font-semibold text-emerald-800 hover:text-emerald-600 dark:text-emerald-300">View business →</a>
                            <form method="POST" action="{{ route('favourites.toggle', $businessProfile) }}">
                                @csrf
                                <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-rose-200 px-3 text-sm font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950/40">♥ Remove</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $businessProfiles->links() }}</div>
        @endif
    </main>
</x-layouts::app>

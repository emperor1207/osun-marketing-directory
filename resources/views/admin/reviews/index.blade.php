<x-layouts::app :title="__('Review moderation')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Administration</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Review moderation</h1>
            <p class="leading-7 text-zinc-600 dark:text-zinc-300">Search, inspect, hide, restore, or remove customer reviews and business responses.</p>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif

        <form method="GET" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 md:grid-cols-[1fr_auto_auto_auto] md:items-end">
            <div>
                <label for="review-search" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Search review, customer, or business</label>
                <input id="review-search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
            </div>
            <div>
                <label for="review-status" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Visibility</label>
                <select id="review-status" name="status" class="min-h-11 rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @foreach (['all' => 'All reviews', 'visible' => 'Visible', 'hidden' => 'Hidden'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="review-rating" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Rating</label>
                <select id="review-rating" name="rating" class="min-h-11 rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">All ratings</option>
                    @foreach (range(1, 5) as $rating)
                        <option value="{{ $rating }}" @selected((string) ($filters['rating'] ?? '') === (string) $rating)>{{ $rating }} stars</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 font-semibold text-white">Filter reviews</button>
        </form>

        @if ($reviews->isEmpty())
            <section class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">No reviews match these filters</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">New submitted reviews will appear here.</p>
            </section>
        @else
            <section class="flex flex-col gap-4">
                @foreach ($reviews as $review)
                    <article class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex min-w-0 flex-col gap-2">
                            <a href="{{ route('admin.reviews.show', $review) }}" class="font-semibold text-emerald-800 hover:underline dark:text-emerald-300">{{ $review->businessProfile->business_name }}</a>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $review->customer->name }} · {{ $review->customer->email }} · {{ $review->rating }} / 5 stars</p>
                            <p class="whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($review->content, 260) }}</p>
                            @if ($review->reply)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Business response: {{ \Illuminate\Support\Str::limit($review->reply->content, 140) }} <span class="font-semibold">({{ $review->reply->status }})</span></p>
                            @endif
                        </div>
                        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $review->status === 'visible' ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200' }}">{{ ucfirst($review->status) }}</span>
                    </article>
                @endforeach
                {{ $reviews->links() }}
            </section>
        @endif
    </main>
</x-layouts::app>

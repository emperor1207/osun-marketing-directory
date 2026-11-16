<x-layouts::app :title="__('Business directory')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-900 p-6 text-white shadow-xl shadow-emerald-950/10 sm:p-9">
            <div aria-hidden="true" class="absolute -end-10 -top-20 size-64 rounded-full border-[36px] border-white/5"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex max-w-2xl flex-col gap-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-200">Osun State</p>
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Business directory</h1>
                    <p class="leading-7 text-emerald-100">Discover approved local businesses and services across Osun State.</p>
                </div>
                <div class="w-fit rounded-2xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-bold">{{ number_format($businessProfiles->total()) }}</p>
                    <p class="text-sm text-emerald-100">{{ \Illuminate\Support\Str::plural('business', $businessProfiles->total()) }} found</p>
                </div>
            </div>
        </header>

        <form method="GET" action="{{ route('directory.index') }}" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6 md:grid-cols-2 lg:grid-cols-4" data-directory-filter-form>
            <div class="md:col-span-2 lg:col-span-4">
                <label for="directory-search" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Search businesses, products or services</label>
                <input id="directory-search" name="search" type="search" maxlength="100" value="{{ $filters['search'] ?? '' }}" placeholder="For example: bread, hair braiding, laptop repair" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-500 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
            </div>

            <div>
                <label for="directory-item-type" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Looking for</label>
                <select id="directory-item-type" name="item_type" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">Products and services</option>
                    <option value="product" @selected(($filters['item_type'] ?? '') === 'product')>Products only</option>
                    <option value="service" @selected(($filters['item_type'] ?? '') === 'service')>Services only</option>
                </select>
            </div>

            <div>
                <label for="directory-category" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Category</label>
                <select id="directory-category" name="category_id" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="directory-lga" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Local government area</label>
                <select id="directory-lga" name="local_government_area_id" data-directory-lga class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">All LGAs</option>
                    @foreach ($localGovernmentAreas as $localGovernmentArea)
                        <option value="{{ $localGovernmentArea->id }}" @selected((string) ($filters['local_government_area_id'] ?? '') === (string) $localGovernmentArea->id)>{{ $localGovernmentArea->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="directory-town" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Town</label>
                <select id="directory-town" name="town_id" data-directory-town class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">All towns</option>
                    @foreach ($towns as $town)
                        <option value="{{ $town->id }}" data-local-government-area="{{ $town->local_government_area_id }}" @selected((string) ($filters['town_id'] ?? '') === (string) $town->id)>{{ $town->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="directory-sort" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Sort by</label>
                <select id="directory-sort" name="sort" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="name" @selected(($filters['sort'] ?? 'name') === 'name')>Name (A to Z)</option>
                    <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Highest rated</option>
                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest</option>
                    <option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>Most visited</option>
                </select>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end md:col-span-2 lg:col-span-4">
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">Apply filters</button>
                <a href="{{ route('directory.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 px-5 py-2.5 text-sm font-medium text-zinc-700 transition hover:border-emerald-400 hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Clear</a>
            </div>
        </form>

        <section class="flex flex-col gap-5" aria-labelledby="directory-results-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Explore Osun</p>
                    <h2 id="directory-results-heading" class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Local businesses</h2>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ number_format($businessProfiles->total()) }} {{ \Illuminate\Support\Str::plural('business', $businessProfiles->total()) }} found</p>
            </div>

            @if ($businessProfiles->isEmpty())
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-emerald-100 text-lg font-bold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" aria-hidden="true">⌕</span>
                    <h3 class="mt-4 text-lg font-semibold text-zinc-950 dark:text-white">No businesses found</h3>
                    @if ($hasActiveFilters)
                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-zinc-600 dark:text-zinc-400">No approved businesses match these filters. Try changing your search or clearing one of the filters.</p>
                        <a href="{{ route('directory.index') }}" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-xl border border-zinc-300 px-4 text-sm font-semibold text-zinc-800 transition hover:border-emerald-500 hover:bg-emerald-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">Clear filters</a>
                    @else
                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-zinc-600 dark:text-zinc-400">No approved businesses have been added to the directory yet. Please check back soon.</p>
                    @endif
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($businessProfiles as $businessProfile)
                        <article class="relative flex min-w-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                            @if ($businessProfile->user_id !== auth()->id())
                                <form method="POST" action="{{ route('favourites.toggle', $businessProfile) }}" class="absolute end-3 top-3 z-10">
                                    @csrf
                                    <button type="submit" aria-label="{{ in_array($businessProfile->id, $favouriteIds, true) ? 'Remove '.$businessProfile->business_name.' from saved businesses' : 'Save '.$businessProfile->business_name }}" class="flex size-10 items-center justify-center rounded-full bg-white/90 text-xl text-rose-600 shadow transition hover:bg-white">{{ in_array($businessProfile->id, $favouriteIds, true) ? '♥' : '♡' }}</button>
                                </form>
                            @endif
                            @if ($businessProfile->cover_image_url)
                                <img src="{{ $businessProfile->cover_image_url }}" alt="" width="720" height="360" loading="lazy" decoding="async" class="h-40 w-full object-cover">
                            @else
                                <div aria-hidden="true" class="h-32 bg-gradient-to-br from-emerald-950 via-emerald-800 to-teal-700"></div>
                            @endif
                            <div class="flex flex-1 flex-col gap-4 p-5">
                                <div class="-mt-10 flex min-w-0 items-end gap-3">
                                    @if ($businessProfile->logo_url)
                                        <img src="{{ $businessProfile->logo_url }}" alt="" width="56" height="56" loading="lazy" decoding="async" class="size-14 shrink-0 rounded-2xl border-4 border-white bg-white object-cover dark:border-zinc-900">
                                    @else
                                        <span aria-hidden="true" class="flex size-14 shrink-0 items-center justify-center rounded-2xl border-4 border-white bg-emerald-100 text-lg font-bold text-emerald-900 dark:border-zinc-900 dark:bg-emerald-950 dark:text-emerald-200">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($businessProfile->business_name, 0, 1)) }}</span>
                                    @endif
                                    <div class="flex min-w-0 flex-col gap-1 pb-1">
                                        <h3 class="truncate text-lg font-semibold text-zinc-950 dark:text-white">{{ $businessProfile->business_name }}</h3>
                                        <p class="truncate text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ $businessProfile->category->name }}</p>
                                    </div>
                                </div>

                                <p class="text-sm font-medium text-amber-700 dark:text-amber-300">
                                    @if ($businessProfile->visible_reviews_count > 0)
                                        <span aria-hidden="true">★</span> {{ number_format((float) $businessProfile->visible_reviews_avg_rating, 1) }} / 5 · {{ number_format($businessProfile->visible_reviews_count) }} {{ \Illuminate\Support\Str::plural('review', $businessProfile->visible_reviews_count) }}
                                    @else
                                        No ratings yet
                                    @endif
                                </p>

                                @if ($businessProfile->description)
                                    <p class="text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit(strip_tags($businessProfile->description), 140) }}</p>
                                @else
                                    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">Contact this business to learn more about its services.</p>
                                @endif

                                @if ($businessProfile->products->isNotEmpty() && (filled($filters['search'] ?? null) || filled($filters['item_type'] ?? null)))
                                    <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/40">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">Matching products &amp; services</p>
                                        <ul class="mt-2 flex flex-col gap-1 text-sm text-zinc-800 dark:text-zinc-200">
                                            @foreach ($businessProfile->products->take(3) as $item)
                                                <li class="flex items-baseline justify-between gap-3">
                                                    <span class="min-w-0 truncate">{{ $item->name }} <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $item->item_type }})</span></span>
                                                    @if ($item->price > 0)
                                                        <span class="shrink-0 font-medium">₦{{ number_format($item->price) }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                        @if ($businessProfile->products->count() > 3)
                                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">+ {{ $businessProfile->products->count() - 3 }} more on the business page</p>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $businessProfile->town->name }}, {{ $businessProfile->town->localGovernmentArea->name }}</p>
                                    <a href="{{ route('directory.show', $businessProfile) }}" class="inline-flex min-h-10 items-center gap-2 rounded-sm text-sm font-semibold text-emerald-800 transition hover:text-emerald-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700 dark:text-emerald-300 dark:hover:text-emerald-200">
                                        View business <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <nav aria-label="Business directory pagination" class="pt-2">
                    {{ $businessProfiles->links() }}
                </nav>
            @endif
        </section>
    </main>
</x-layouts::app>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => 'Osun Marketing Directory'])
    </head>
    <body class="min-h-screen bg-stone-50 text-zinc-900 antialiased">
        <header class="sticky top-0 z-30 border-b border-zinc-200/80 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <a href="{{ route('home') }}" class="flex w-fit items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700" aria-label="Osun Marketing Directory home">
                    <img
                        src="{{ asset('images/osun-marketing-directory-logo.svg') }}"
                        alt="Osun Marketing Directory. Discover. Connect. Grow."
                        width="286"
                        height="48"
                        class="h-11 w-auto max-w-[min(70vw,18rem)]"
                    >
                </a>

                <nav aria-label="Main navigation" class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium">
                    <a href="{{ route('home') }}" aria-current="page" class="rounded-sm text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">Home</a>
                    <a href="{{ route('directory.index') }}" class="rounded-sm text-zinc-700 transition hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">Directory</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-sm text-zinc-700 transition hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-sm text-zinc-700 transition hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">Login</a>
                        <a href="{{ route('register') }}" class="inline-flex min-h-10 items-center rounded-full bg-emerald-800 px-5 font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">Create an account</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            <section class="relative isolate flex min-h-[620px] items-center overflow-hidden bg-emerald-950 text-white sm:min-h-[660px]">
                <img
                    src="{{ asset('images/osun-grove-landscape.webp') }}"
                    alt=""
                    aria-hidden="true"
                    width="1000"
                    height="668"
                    fetchpriority="high"
                    decoding="async"
                    class="absolute inset-0 size-full object-cover object-center"
                >
                <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-r from-emerald-950/95 via-emerald-950/80 to-emerald-950/45"></div>
                <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-t from-emerald-950/65 via-transparent to-emerald-950/15"></div>

                <div class="relative mx-auto grid w-full max-w-7xl gap-10 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.1fr_0.7fr] lg:items-center lg:px-8 lg:py-24">
                    <div class="flex flex-col items-start gap-7">
                        <p class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-sm font-semibold text-emerald-50 backdrop-blur">
                            <span class="size-2 rounded-full bg-amber-300" aria-hidden="true"></span>
                            Made for communities across Osun State
                        </p>
                        <div class="flex flex-col gap-5">
                            <h1 class="max-w-3xl text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                                Find local businesses and services across Osun State.
                            </h1>
                            <p class="max-w-2xl text-lg leading-8 text-emerald-50/90">
                                Osun Marketing Directory connects people with businesses in their communities, making it easier to discover local services and what they offer.
                            </p>
                        </div>
                        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                            <a href="{{ route('directory.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-amber-300 px-6 py-3 font-semibold text-emerald-950 transition hover:bg-amber-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                Discover businesses
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/50 bg-white/5 px-6 py-3 font-semibold text-white transition hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                Register your business
                            </a>
                        </div>
                        <p class="max-w-xl text-sm leading-6 text-emerald-100">
                            Sign in to search approved listings, explore locations and contact businesses directly.
                        </p>
                    </div>

                    <aside class="flex flex-col gap-5 rounded-3xl border border-white/20 bg-emerald-950/55 p-5 shadow-2xl shadow-black/20 backdrop-blur-md sm:p-7" aria-label="Directory overview">
                        <div class="flex flex-col gap-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-200">Discover Osun</p>
                            <h2 class="text-2xl font-bold">Local knowledge, easier to find.</h2>
                            <p class="text-sm leading-6 text-emerald-50/85">Explore approved businesses across the state, one community at a time.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <p class="text-3xl font-bold tracking-tight">{{ number_format($approvedBusinessCount) }}</p>
                                <p class="mt-1 text-sm text-emerald-100">approved listings</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <p class="text-3xl font-bold tracking-tight">{{ number_format($localGovernmentAreaCount) }}</p>
                                <p class="mt-1 text-sm text-emerald-100">active LGAs</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-black/15 p-4">
                            <p class="text-sm font-semibold text-white">A directory rooted in Osun</p>
                            <p class="mt-2 text-sm leading-6 text-emerald-100/85">Find nearby services, see where a business is located and use the contact details it provides.</p>
                            <a href="{{ route('directory.index') }}" class="mt-4 inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-amber-200 transition hover:text-amber-100 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                                Explore the directory <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </aside>
                </div>
                <span class="sr-only">Background photograph: Osun-Osogbo Sacred Grove, Osun State.</span>
            </section>

            <section class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-[0.75fr_1.25fr] lg:items-center lg:px-8 lg:py-16" aria-labelledby="homepage-search-heading">
                <div class="flex flex-col gap-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-800">Search the directory</p>
                    <h2 id="homepage-search-heading" class="text-3xl font-bold tracking-tight text-emerald-950">Who are you looking for?</h2>
                    <p class="leading-7 text-zinc-600">Search for a product, a service or a business name, then refine your results by category and location.</p>
                </div>
                <form method="GET" action="{{ route('directory.index') }}" class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-lg shadow-emerald-950/5 sm:flex-row sm:items-center sm:p-5">
                    <label for="homepage-search" class="sr-only">Business, product or service</label>
                    <input
                        id="homepage-search"
                        name="search"
                        type="search"
                        maxlength="100"
                        placeholder="Try a product or service, such as bread or tailoring"
                        class="min-h-12 w-full rounded-xl border border-zinc-300 bg-stone-50 px-4 text-zinc-900 outline-none transition placeholder:text-zinc-500 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20"
                    >
                    <button type="submit" class="min-h-12 shrink-0 rounded-xl bg-emerald-800 px-6 font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                        Search businesses
                    </button>
                </form>
            </section>

            <section class="border-y border-zinc-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div class="flex flex-col gap-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-800">Explore by category</p>
                            <h2 class="text-3xl font-bold tracking-tight text-emerald-950">Find the right local service</h2>
                        </div>
                        <a href="{{ route('directory.index') }}" class="w-fit rounded-sm font-semibold text-emerald-800 transition hover:text-emerald-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">View the full directory <span aria-hidden="true">→</span></a>
                    </div>

                    @if ($categories->isNotEmpty())
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($categories as $category)
                                <a
                                    href="{{ route('directory.index', ['category_id' => $category->id]) }}"
                                    class="group flex min-h-32 flex-col justify-between gap-5 rounded-2xl border border-zinc-200 bg-stone-50 p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                                >
                                    <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 text-lg font-bold text-emerald-900" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name, 0, 1)) }}</span>
                                    <span class="flex items-end justify-between gap-3">
                                        <span class="font-semibold text-zinc-950 group-hover:text-emerald-900">{{ $category->name }}</span>
                                        <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-medium text-zinc-600">{{ number_format($category->approved_businesses_count) }} {{ \Illuminate\Support\Str::plural('listing', $category->approved_businesses_count) }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="rounded-2xl border border-dashed border-zinc-300 bg-stone-50 p-6 text-zinc-600">
                            Categories will appear here as businesses are approved for the directory.
                        </p>
                    @endif
                </div>
            </section>

            <section class="mx-auto flex max-w-7xl flex-col gap-7 px-4 py-14 sm:px-6 lg:px-8 lg:py-16" aria-labelledby="homepage-recent-heading">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-800">Recently added</p>
                    <h2 id="homepage-recent-heading" class="text-3xl font-bold tracking-tight text-emerald-950">Businesses in the directory</h2>
                    <p class="max-w-2xl leading-7 text-zinc-600">These listings are visible because they have been approved for publication.</p>
                </div>

                @if ($recentBusinesses->isNotEmpty())
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($recentBusinesses as $business)
                            <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                                @if ($business->cover_image_url)
                                    <img src="{{ $business->cover_image_url }}" alt="" width="640" height="360" loading="lazy" decoding="async" class="h-40 w-full object-cover">
                                @else
                                    <div aria-hidden="true" class="h-32 bg-gradient-to-br from-emerald-900 via-emerald-700 to-teal-600"></div>
                                @endif
                                <div class="flex flex-1 flex-col gap-4 p-5">
                                    <div class="-mt-10 flex items-end gap-3">
                                        @if ($business->logo_url)
                                            <img src="{{ $business->logo_url }}" alt="" width="56" height="56" loading="lazy" decoding="async" class="size-14 rounded-2xl border-4 border-white bg-white object-cover">
                                        @else
                                            <span aria-hidden="true" class="flex size-14 items-center justify-center rounded-2xl border-4 border-white bg-emerald-100 text-lg font-bold text-emerald-900">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->business_name, 0, 1)) }}
                                            </span>
                                        @endif
                                        <span class="pb-1 text-xs font-semibold uppercase tracking-wide text-emerald-800">{{ $business->category->name }}</span>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h3 class="truncate text-lg font-semibold text-zinc-950">{{ $business->business_name }}</h3>
                                        <p class="text-sm text-zinc-600">{{ $business->town->name }}, {{ $business->town->localGovernmentArea->name }}</p>
                                    </div>
                                    <a href="{{ route('directory.show', $business) }}" class="mt-auto inline-flex min-h-10 items-center gap-2 font-semibold text-emerald-800 transition hover:text-emerald-600 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">
                                        View business <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <a href="{{ route('directory.index') }}" class="inline-flex min-h-11 w-fit items-center justify-center rounded-xl border border-zinc-300 px-5 font-semibold text-zinc-800 transition hover:border-emerald-500 hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">Browse all businesses</a>
                @else
                    <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-10 text-center">
                        <p class="font-semibold text-zinc-900">No approved business listings are available yet.</p>
                        <p class="mt-2 text-sm text-zinc-600">Approved listings will appear here after administrator review.</p>
                    </div>
                @endif
            </section>

            <section class="border-y border-emerald-950/10 bg-emerald-50/70">
                <div class="mx-auto grid max-w-7xl gap-5 px-4 py-14 sm:px-6 md:grid-cols-2 lg:px-8 lg:py-16">
                    <article class="flex flex-col items-start gap-4 rounded-3xl bg-gradient-to-br from-emerald-900 to-emerald-800 p-7 text-white shadow-lg shadow-emerald-950/10 sm:p-9">
                        <span class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-200">For customers</span>
                        <h2 class="text-2xl font-bold">Discover businesses near you</h2>
                        <p class="leading-7 text-emerald-100">Search approved listings and find contact and location details for businesses across Osun State.</p>
                        <a href="{{ route('directory.index') }}" class="mt-auto inline-flex min-h-11 items-center rounded-xl bg-white px-5 font-semibold text-emerald-950 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Browse the directory</a>
                    </article>
                    <article class="flex flex-col items-start gap-4 rounded-3xl border border-amber-200 bg-amber-100 p-7 text-emerald-950 shadow-sm sm:p-9">
                        <span class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-800">For business owners</span>
                        <h2 class="text-2xl font-bold">Help more people find your business</h2>
                        <p class="leading-7 text-emerald-950/75">Create a business-owner account and submit your profile for directory review.</p>
                        <a href="{{ route('register') }}" class="mt-auto inline-flex min-h-11 items-center rounded-xl bg-emerald-900 px-5 font-semibold text-white transition hover:bg-emerald-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-800">Register your business</a>
                    </article>
                </div>
            </section>
        </main>

        <footer class="bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-8 text-sm text-zinc-600 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <a href="{{ route('home') }}" class="w-fit rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">
                    <img
                        src="{{ asset('images/osun-marketing-directory-logo.svg') }}"
                        alt="Osun Marketing Directory. Discover. Connect. Grow."
                        width="286"
                        height="48"
                        loading="lazy"
                        decoding="async"
                        class="h-10 w-auto max-w-[min(70vw,18rem)]"
                    >
                </a>
                <p>Connecting people with local businesses across Osun State.</p>
                <p class="max-w-xl text-xs leading-5 text-zinc-500">
                    Osun-Osogbo Sacred Grove image by
                    <a href="https://commons.wikimedia.org/wiki/File:Osun-Osogbo_sacred_groove.jpg" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-800 underline underline-offset-2">Hadassah Photostorie group</a>
                    via Wikimedia Commons, licensed under
                    <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-800 underline underline-offset-2">CC BY-SA 4.0</a>.
                </p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>

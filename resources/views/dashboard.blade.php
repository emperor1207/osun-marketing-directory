<x-layouts::app :title="__('Dashboard')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">
                {{ $user->isAdmin() ? 'Administration workspace' : ($user->isBusinessOwner() ? 'Business workspace' : 'Osun Marketing Directory') }}
            </p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white sm:text-4xl">
                Welcome back, {{ $user->name }}
            </h1>
            <p class="max-w-2xl leading-7 text-zinc-600 dark:text-zinc-300">
                @if ($user->isAdmin())
                    Review directory submissions and keep local business information up to date.
                @elseif ($user->isBusinessOwner())
                    Manage your business profile and help customers discover what you offer.
                @else
                    Find local businesses and services from communities across Osun State.
                @endif
            </p>
        </header>

        @if ($user->isAdmin())
            <section class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-900 p-6 text-white shadow-xl shadow-emerald-950/10 sm:p-9" aria-labelledby="admin-workspace-heading">
                <div aria-hidden="true" class="absolute -end-10 -top-20 size-72 rounded-full border-[40px] border-white/5"></div>
                <div aria-hidden="true" class="absolute -bottom-36 end-40 size-72 rounded-full bg-emerald-400/10 blur-3xl"></div>
                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex max-w-2xl flex-col gap-3">
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-200">Administrator</p>
                        <h2 id="admin-workspace-heading" class="text-2xl font-bold tracking-tight sm:text-3xl">Keep the directory useful and trusted.</h2>
                        <p class="leading-7 text-emerald-100">Review business profiles, maintain categories and keep Osun locations organised from one workspace.</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-xl bg-amber-300 px-5 font-semibold text-emerald-950 transition hover:bg-amber-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        Open administration
                    </a>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Administration shortcuts">
                <a href="{{ route('admin.profiles.index', ['status' => 'pending']) }}" class="group flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-800 dark:bg-zinc-900">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-100 font-bold text-amber-900" aria-hidden="true">01</span>
                    <span class="flex flex-col gap-1">
                        <span class="font-semibold text-zinc-950 group-hover:text-emerald-800 dark:text-white dark:group-hover:text-emerald-300">Review submissions</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Check pending business profiles.</span>
                    </span>
                </a>
                <a href="{{ route('admin.categories') }}" class="group flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-800 dark:bg-zinc-900">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-100 font-bold text-emerald-900" aria-hidden="true">02</span>
                    <span class="flex flex-col gap-1">
                        <span class="font-semibold text-zinc-950 group-hover:text-emerald-800 dark:text-white dark:group-hover:text-emerald-300">Manage categories</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Keep directory classifications current.</span>
                    </span>
                </a>
                <a href="{{ route('admin.locations') }}" class="group flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-800 dark:bg-zinc-900">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-sky-100 font-bold text-sky-900" aria-hidden="true">03</span>
                    <span class="flex flex-col gap-1">
                        <span class="font-semibold text-zinc-950 group-hover:text-emerald-800 dark:text-white dark:group-hover:text-emerald-300">Manage locations</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Update LGAs and towns.</span>
                    </span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="group flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-800 dark:bg-zinc-900">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-violet-100 font-bold text-violet-900" aria-hidden="true">04</span>
                    <span class="flex flex-col gap-1">
                        <span class="font-semibold text-zinc-950 group-hover:text-emerald-800 dark:text-white dark:group-hover:text-emerald-300">Manage accounts</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Update customer and owner access.</span>
                    </span>
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="group flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-800 dark:bg-zinc-900">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-100 font-bold text-amber-900" aria-hidden="true">05</span>
                    <span class="flex flex-col gap-1">
                        <span class="font-semibold text-zinc-950 group-hover:text-emerald-800 dark:text-white dark:group-hover:text-emerald-300">Moderate reviews</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Manage customer feedback and owner replies.</span>
                    </span>
                </a>
                <a href="{{ route('admin.business-reports.index') }}" class="group flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-800 dark:bg-zinc-900">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-rose-100 font-bold text-rose-900" aria-hidden="true">06</span>
                    <span class="flex flex-col gap-1">
                        <span class="font-semibold text-zinc-950 group-hover:text-emerald-800 dark:text-white dark:group-hover:text-emerald-300">Resolve listing reports</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Review customer-reported listing concerns.</span>
                    </span>
                </a>
            </section>
        @elseif ($user->isBusinessOwner())
            <section class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-900 text-white shadow-xl shadow-emerald-950/10">
                @if ($businessProfile?->cover_image_url)
                    <img
                        src="{{ $businessProfile->cover_image_url }}"
                        alt=""
                        width="1200"
                        height="500"
                        loading="lazy"
                        decoding="async"
                        class="absolute inset-0 size-full object-cover"
                    >
                @endif
                <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-r from-emerald-950/95 via-emerald-950/85 to-emerald-950/55"></div>
                <div aria-hidden="true" class="absolute -bottom-24 -end-12 size-64 rounded-full border-[36px] border-white/10"></div>
                <div class="relative flex flex-col gap-6 p-6 sm:p-9 lg:flex-row lg:items-end lg:justify-between">
                    <div class="flex max-w-2xl flex-col gap-3">
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-200">Business owner</p>
                        <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $businessProfile?->business_name ?? 'Your business profile' }}</h2>
                        <p class="leading-7 text-emerald-100">
                            @if ($businessProfile?->status === 'approved')
                                Your business is approved and visible in the directory.
                            @elseif ($businessProfile?->status === 'pending')
                                Your profile is with the administrator for review.
                            @elseif ($businessProfile?->status === 'rejected')
                                Review the feedback, update your profile and submit it again.
                            @else
                                Create a profile to introduce your business to customers across Osun State.
                            @endif
                        </p>
                    </div>
                    @if ($businessProfile)
                        <span @class([
                            'inline-flex w-fit shrink-0 items-center rounded-full px-4 py-2 text-sm font-semibold capitalize',
                            'bg-amber-200 text-amber-950' => $businessProfile->status === 'pending',
                            'bg-emerald-200 text-emerald-950' => $businessProfile->status === 'approved',
                            'bg-rose-200 text-rose-950' => $businessProfile->status === 'rejected',
                        ])>
                            {{ ucfirst($businessProfile->status) }}
                        </span>
                    @endif
                </div>
            </section>

            @if ($businessProfile)
                <section class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
                        <div class="flex items-start gap-4">
                            @if ($businessProfile->logo_url)
                                <img src="{{ $businessProfile->logo_url }}" alt="" width="64" height="64" loading="lazy" decoding="async" class="size-16 shrink-0 rounded-2xl border border-zinc-200 object-cover dark:border-zinc-700">
                            @else
                                <span aria-hidden="true" class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-xl font-bold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($businessProfile->business_name, 0, 1)) }}
                                </span>
                            @endif
                            <div class="min-w-0">
                                <h3 class="truncate text-lg font-semibold text-zinc-950 dark:text-white">{{ $businessProfile->business_name }}</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $businessProfile->category->name }} <span aria-hidden="true">·</span> {{ $businessProfile->town->name }}</p>
                            </div>
                        </div>

                        @if ($businessProfile->status === 'rejected' && $businessProfile->rejection_reason)
                            <div role="note" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm leading-6 text-rose-900 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-200">
                                <span class="font-semibold">Administrator feedback:</span> {{ $businessProfile->rejection_reason }}
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('business-profile.show') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                                View my profile
                            </a>
                            <a href="{{ route('business-profile.edit') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 px-5 font-semibold text-zinc-800 transition hover:border-emerald-500 hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">
                                {{ $businessProfile->status === 'rejected' ? 'Update and resubmit' : 'Manage listing' }}
                            </a>
                            <a href="{{ route('business-products.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 px-5 font-semibold text-zinc-800 transition hover:border-emerald-500 hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">
                                Manage products &amp; services
                            </a>
                            @if ($businessProfile->status === 'approved')
                                <a href="{{ route('directory.show', $businessProfile) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 px-5 font-semibold text-zinc-800 transition hover:border-emerald-500 hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">
                                    View public listing
                                </a>
                            @endif
                        </div>
                    </article>

                    <aside class="flex flex-col gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900/70 dark:bg-emerald-950/40 sm:p-7">
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-800 dark:text-emerald-300">Profile details</p>
                        <dl class="grid gap-4">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Review status</dt>
                                <dd class="mt-1 font-semibold capitalize text-zinc-900 dark:text-white">{{ ucfirst($businessProfile->status) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Location</dt>
                                <dd class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $businessProfile->town->name }}, Osun State</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Contact details</dt>
                                <dd class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ collect([$businessProfile->phone, $businessProfile->email, $businessProfile->website])->filter()->count() }} provided</dd>
                            </div>
                        </dl>
                        <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">Keep your business description, contact details and location accurate so customers can reach you.</p>
                    </aside>
                </section>
            @else
                <section class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="flex flex-col items-start gap-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-emerald-100 text-lg font-bold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" aria-hidden="true">01</span>
                        <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">Create your business profile</h3>
                        <p class="max-w-xl leading-7 text-zinc-600 dark:text-zinc-300">Add your business name, category, location and contact information. An administrator reviews each submission before it appears in the directory.</p>
                        <a href="{{ route('business-profile.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">Create business profile</a>
                    </article>
                    <aside class="flex flex-col justify-center gap-3 rounded-2xl bg-amber-100 p-6 text-emerald-950 sm:p-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-800">Listing guidance</p>
                        <h3 class="text-xl font-bold">Start with the details customers need.</h3>
                        <p class="leading-7 text-emerald-950/75">A clear description, working contact information and accurate town help people find and reach your business.</p>
                    </aside>
                </section>
            @endif

            @if ($businessProfile)
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Business performance">
                    @foreach ([
                        ['label' => 'Visible reviews', 'value' => $ownerReviewCount],
                        ['label' => 'Average rating', 'value' => $ownerReviewCount ? number_format((float) $ownerAverageRating, 1).' / 5' : 'Not rated'],
                        ['label' => 'Profile views', 'value' => number_format($ownerProfileViewCount)],
                        ['label' => 'Views in last 30 days', 'value' => number_format($ownerRecentViewCount)],
                    ] as $statistic)
                        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ $statistic['label'] }}</p>
                            <p class="mt-3 text-2xl font-bold text-zinc-950 dark:text-white">{{ $statistic['value'] }}</p>
                        </article>
                    @endforeach
                </section>

                <section class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7" aria-labelledby="owner-review-activity-heading">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Customer feedback</p>
                            <h2 id="owner-review-activity-heading" class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Recent reviews</h2>
                        </div>
                        <a href="{{ route('business-reviews.index') }}" class="text-sm font-semibold text-emerald-800 underline dark:text-emerald-300">Manage reviews and responses</a>
                    </div>
                    @if ($ownerRecentReviews->isEmpty())
                        <p class="rounded-xl border border-dashed border-zinc-300 p-5 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">No customer reviews have been submitted for this listing yet.</p>
                    @else
                        <div class="grid gap-3 md:grid-cols-3">
                            @foreach ($ownerRecentReviews as $review)
                                <article class="flex flex-col gap-2 rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800">
                                    <p class="font-semibold text-zinc-950 dark:text-white">{{ $review->customer->name }} · {{ $review->rating }} / 5</p>
                                    <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($review->content, 120) }}</p>
                                    <p class="mt-auto text-xs {{ $review->reply ? 'text-emerald-800 dark:text-emerald-300' : 'text-amber-800 dark:text-amber-300' }}">{{ $review->reply ? 'You have responded' : 'Response needed' }}</p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif
        @else
            <section class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-900 p-6 text-white shadow-xl shadow-emerald-950/10 sm:p-9" aria-labelledby="customer-discovery-heading">
                <div aria-hidden="true" class="absolute -end-12 -top-20 size-72 rounded-full border-[40px] border-white/5"></div>
                <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div class="flex max-w-2xl flex-col gap-3">
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-200">Customer dashboard</p>
                        <h2 id="customer-discovery-heading" class="text-2xl font-bold tracking-tight sm:text-3xl">Discover local businesses across Osun.</h2>
                        <p class="leading-7 text-emerald-100">Search for a product, a service or a business name, or start with a category. Browse approved listings and contact businesses directly.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div class="min-w-32 rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <p class="text-2xl font-bold">{{ number_format($approvedBusinessCount) }}</p>
                            <p class="mt-1 text-xs font-medium text-emerald-100">approved businesses</p>
                        </div>
                        <div class="min-w-32 rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <p class="text-2xl font-bold">{{ number_format($categories->count()) }}</p>
                            <p class="mt-1 text-xs font-medium text-emerald-100">categories with listings</p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('directory.index') }}" class="relative mt-7 flex flex-col gap-3 rounded-2xl bg-white p-3 shadow-lg sm:flex-row sm:items-center">
                    <label for="dashboard-search" class="sr-only">Search businesses, products or services</label>
                    <input id="dashboard-search" name="search" type="search" maxlength="100" placeholder="Try &quot;bread&quot;, &quot;hair braiding&quot; or &quot;laptop repair&quot;" class="min-h-12 w-full rounded-xl border-0 bg-zinc-50 px-4 text-zinc-950 outline-none ring-1 ring-zinc-200 transition placeholder:text-zinc-500 focus:ring-2 focus:ring-emerald-700">
                    <button type="submit" class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-xl bg-amber-300 px-6 font-semibold text-emerald-950 transition hover:bg-amber-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">Search</button>
                </form>
                <a href="{{ route('directory.index') }}" class="relative mt-1 inline-flex min-h-10 w-fit items-center gap-2 font-semibold text-emerald-100 transition hover:text-white focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                    Browse the business directory <span aria-hidden="true">→</span>
                </a>
            </section>

            <section class="flex flex-col gap-5" aria-labelledby="customer-categories-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Start exploring</p>
                        <h2 id="customer-categories-heading" class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Browse by category</h2>
                    </div>
                    <a href="{{ route('directory.index') }}" class="text-sm font-semibold text-emerald-800 transition hover:text-emerald-600 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700 dark:text-emerald-300">Open the full directory <span aria-hidden="true">→</span></a>
                </div>

                @if ($categories->isNotEmpty())
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($categories as $category)
                            <a href="{{ route('directory.index', ['category_id' => $category->id]) }}" class="group flex min-h-28 items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-800 dark:bg-zinc-900">
                                <span class="flex min-w-0 items-center gap-4">
                                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-lg font-bold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name, 0, 1)) }}</span>
                                    <span class="flex min-w-0 flex-col gap-1">
                                        <span class="truncate font-semibold text-zinc-950 group-hover:text-emerald-800 dark:text-white dark:group-hover:text-emerald-300">{{ $category->name }}</span>
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ number_format($category->approved_businesses_count) }} {{ \Illuminate\Support\Str::plural('listing', $category->approved_businesses_count) }}</span>
                                    </span>
                                </span>
                                <span class="text-lg text-zinc-400 transition group-hover:translate-x-1 group-hover:text-emerald-700" aria-hidden="true">→</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <p class="font-semibold text-zinc-900 dark:text-white">Categories will appear as approved businesses are added.</p>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">You can still search the directory or browse all current listings.</p>
                    </div>
                @endif
            </section>

            <section class="flex flex-col gap-5" aria-labelledby="recent-businesses-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Recently added</p>
                        <h2 id="recent-businesses-heading" class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Meet businesses in your community</h2>
                    </div>
                    <a href="{{ route('directory.index') }}" class="text-sm font-semibold text-emerald-800 transition hover:text-emerald-600 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700 dark:text-emerald-300">Browse all businesses <span aria-hidden="true">→</span></a>
                </div>

                @if ($recentBusinesses->isNotEmpty())
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($recentBusinesses as $business)
                            <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                                @if ($business->cover_image_url)
                                    <img src="{{ $business->cover_image_url }}" alt="" width="640" height="360" loading="lazy" decoding="async" class="h-36 w-full object-cover">
                                @else
                                    <div aria-hidden="true" class="h-28 bg-gradient-to-br from-emerald-900 via-emerald-700 to-teal-600"></div>
                                @endif
                                <div class="flex flex-1 flex-col gap-4 p-5">
                                    <div class="-mt-10 flex items-end gap-3">
                                        @if ($business->logo_url)
                                            <img src="{{ $business->logo_url }}" alt="" width="56" height="56" loading="lazy" decoding="async" class="size-14 rounded-2xl border-4 border-white bg-white object-cover dark:border-zinc-900">
                                        @else
                                            <span aria-hidden="true" class="flex size-14 items-center justify-center rounded-2xl border-4 border-white bg-emerald-100 text-lg font-bold text-emerald-900 dark:border-zinc-900 dark:bg-emerald-950 dark:text-emerald-200">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($business->business_name, 0, 1)) }}
                                            </span>
                                        @endif
                                        <span class="pb-1 text-xs font-semibold uppercase tracking-wide text-emerald-800 dark:text-emerald-300">{{ $business->category->name }}</span>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h3 class="truncate text-lg font-semibold text-zinc-950 dark:text-white">{{ $business->business_name }}</h3>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $business->town->name }}, {{ $business->town->localGovernmentArea->name }}</p>
                                    </div>
                                    <a href="{{ route('directory.show', $business) }}" class="mt-auto inline-flex min-h-10 items-center gap-2 font-semibold text-emerald-800 transition hover:text-emerald-600 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700 dark:text-emerald-300">
                                        View business <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <p class="font-semibold text-zinc-900 dark:text-white">No approved businesses have been added to the directory yet.</p>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">New listings will appear here after administrator review.</p>
                        <a href="{{ route('directory.index') }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-xl border border-zinc-300 px-4 font-semibold text-zinc-800 transition hover:border-emerald-500 hover:bg-emerald-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">Browse directory</a>
                    </div>
                @endif
            </section>

            <section class="grid gap-5 lg:grid-cols-2" aria-label="Customer activity">
                <article class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
                    <header class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Your feedback</p>
                            <h2 class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Recent reviews</h2>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ number_format($customerReviewCount) }}</span>
                    </header>
                    @if ($recentReviews->isEmpty())
                        <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-400">You have not reviewed a business yet. Open an approved listing to share your experience.</p>
                    @else
                        <div class="flex flex-col gap-3">
                            @foreach ($recentReviews as $review)
                                <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800">
                                    <a href="{{ route('directory.show', ['businessProfile' => $review->business_profile_id]) }}" class="font-semibold text-emerald-800 underline dark:text-emerald-300">{{ $review->businessProfile?->business_name ?? 'View business' }}</a>
                                    <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $review->rating }} / 5 · {{ \Illuminate\Support\Str::limit($review->content, 120) }}</p>
                                    <p class="mt-1 text-xs capitalize text-zinc-500 dark:text-zinc-400">Review status: {{ $review->status }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
                <article class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
                    <header class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-amber-700 dark:text-amber-300">Listing concerns</p>
                            <h2 class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">Your reports</h2>
                        </div>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ number_format($customerReportCount) }}</span>
                    </header>
                    @if ($recentReports->isEmpty())
                        <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-400">You have not reported a business listing.</p>
                    @else
                        <div class="flex flex-col gap-3">
                            @foreach ($recentReports as $report)
                                <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800">
                                    <p class="font-semibold text-zinc-950 dark:text-white">{{ $report->businessProfile->business_name }}</p>
                                    <p class="mt-1 text-sm capitalize text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::replace('_', ' ', $report->status) }} · {{ \Illuminate\Support\Str::replace('_', ' ', $report->reason) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <a href="{{ route('business-reports.mine') }}" class="mt-auto text-sm font-semibold text-emerald-800 underline dark:text-emerald-300">View all reports</a>
                </article>
            </section>
        @endif
    </main>
</x-layouts::app>

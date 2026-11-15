<x-layouts::app :title="__('Administration dashboard')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-900 p-6 text-white shadow-xl shadow-emerald-950/10 sm:p-9">
            <div aria-hidden="true" class="absolute -end-12 -top-20 size-72 rounded-full border-[40px] border-white/5"></div>
            <div aria-hidden="true" class="absolute -bottom-28 end-1/3 size-64 rounded-full bg-emerald-400/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex max-w-2xl flex-col gap-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-200">Osun Marketing Directory</p>
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Administration dashboard</h1>
                    <p class="leading-7 text-emerald-100">Monitor directory activity, review new business submissions and manage the Osun catalog.</p>
                </div>
                <a href="{{ route('admin.profiles.index', ['status' => 'pending']) }}" class="inline-flex min-h-11 w-fit shrink-0 items-center justify-center rounded-xl bg-amber-300 px-5 font-semibold text-emerald-950 transition hover:bg-amber-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    Review pending profiles
                </a>
            </div>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" aria-label="Directory statistics">
            @foreach ([
                ['label' => 'Total users', 'value' => $statistics['users'], 'tone' => 'emerald'],
                ['label' => 'Active customers', 'value' => $statistics['active_customers'], 'tone' => 'sky'],
                ['label' => 'Active business owners', 'value' => $statistics['active_business_owners'], 'tone' => 'teal'],
                ['label' => 'Active administrators', 'value' => $statistics['active_administrators'], 'tone' => 'violet'],
                ['label' => 'Business profiles', 'value' => $statistics['business_profiles'], 'tone' => 'violet'],
                ['label' => 'Pending approvals', 'value' => $statistics['pending_profiles'], 'tone' => 'amber'],
                ['label' => 'Approved businesses', 'value' => $statistics['approved_profiles'], 'tone' => 'teal'],
                ['label' => 'Visible reviews', 'value' => $statistics['visible_reviews'], 'tone' => 'emerald'],
                ['label' => 'Hidden reviews', 'value' => $statistics['hidden_reviews'], 'tone' => 'sky'],
                ['label' => 'Reports submitted', 'value' => $statistics['submitted_reports'], 'tone' => 'amber'],
                ['label' => 'Reports under review', 'value' => $statistics['reports_under_review'], 'tone' => 'violet'],
                ['label' => 'Profile views', 'value' => $statistics['total_profile_views'], 'tone' => 'teal'],
            ] as $statistic)
                <article class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <span @class([
                        'absolute inset-x-0 top-0 h-1',
                        'bg-emerald-500' => $statistic['tone'] === 'emerald',
                        'bg-sky-500' => $statistic['tone'] === 'sky',
                        'bg-violet-500' => $statistic['tone'] === 'violet',
                        'bg-amber-500' => $statistic['tone'] === 'amber',
                        'bg-teal-500' => $statistic['tone'] === 'teal',
                    ])></span>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ $statistic['label'] }}</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">{{ number_format($statistic['value']) }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(18rem,0.75fr)]">
            <div class="flex min-w-0 flex-col gap-5" aria-labelledby="pending-profiles-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div class="flex flex-col gap-1">
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-amber-700 dark:text-amber-300">Review queue</p>
                        <h2 id="pending-profiles-heading" class="text-xl font-bold text-zinc-950 dark:text-white">Pending business profiles</h2>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Newest submissions awaiting an administrator decision.</p>
                    </div>
                    <a href="{{ route('admin.profiles.index', ['status' => 'pending']) }}" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-950">
                        View all pending
                    </a>
                </div>

                @if ($pendingProfiles->isEmpty())
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-emerald-100 text-lg font-bold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" aria-hidden="true">✓</span>
                        <h3 class="font-semibold text-zinc-950 dark:text-white">The review queue is clear.</h3>
                        <p class="max-w-md text-sm leading-6 text-zinc-600 dark:text-zinc-400">New business submissions will appear here after owners create their profiles.</p>
                        <a href="{{ route('admin.profiles.index', ['status' => 'all']) }}" class="mt-1 inline-flex min-h-10 items-center rounded-lg border border-zinc-300 px-4 text-sm font-semibold text-zinc-800 transition hover:border-emerald-500 hover:bg-emerald-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">Browse all profiles</a>
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($pendingProfiles as $businessProfile)
                            <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                                @if ($businessProfile->cover_image_url)
                                    <img src="{{ $businessProfile->cover_image_url }}" alt="" width="720" height="360" loading="lazy" decoding="async" class="h-36 w-full object-cover">
                                @else
                                    <div aria-hidden="true" class="h-28 bg-gradient-to-br from-emerald-950 via-emerald-800 to-teal-700"></div>
                                @endif
                                <div class="flex flex-1 flex-col gap-4 p-5">
                                    <div class="-mt-10 flex min-w-0 items-end justify-between gap-3">
                                        @if ($businessProfile->logo_url)
                                            <img src="{{ $businessProfile->logo_url }}" alt="" width="56" height="56" loading="lazy" decoding="async" class="size-14 rounded-2xl border-4 border-white bg-white object-cover dark:border-zinc-900">
                                        @else
                                            <span aria-hidden="true" class="flex size-14 items-center justify-center rounded-2xl border-4 border-white bg-emerald-100 text-lg font-bold text-emerald-900 dark:border-zinc-900 dark:bg-emerald-950 dark:text-emerald-200">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($businessProfile->business_name, 0, 1)) }}
                                            </span>
                                        @endif
                                        <span class="mb-1 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900 dark:bg-amber-950 dark:text-amber-200">Pending review</span>
                                    </div>

                                    <div class="flex min-w-0 flex-col gap-1">
                                        <h3 class="truncate text-lg font-semibold text-zinc-950 dark:text-white">{{ $businessProfile->business_name }}</h3>
                                        <p class="text-sm text-emerald-800 dark:text-emerald-300">{{ $businessProfile->category->name }}</p>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $businessProfile->town->name }}, {{ $businessProfile->town->localGovernmentArea->name }}</p>
                                    </div>

                                    <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($businessProfile->description ?? 'No description provided.', 140) }}</p>

                                    <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $businessProfile->owner->name }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Submitted {{ $businessProfile->created_at->format('M j, Y') }}</p>
                                        </div>
                                        <a href="{{ route('admin.profiles.show', $businessProfile) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-emerald-800 px-4 text-sm font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                                            Review submission
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    {{ $pendingProfiles->links() }}
                @endif
            </div>

            <aside class="flex flex-col gap-4" aria-label="Directory management">
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Directory management</p>
                    <h2 class="mt-2 text-lg font-bold text-zinc-950 dark:text-white">Keep the catalog current.</h2>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">These shortcuts open the existing catalog management pages.</p>
                    <div class="mt-5 flex flex-col gap-2">
                        <a href="{{ route('admin.profiles.index', ['status' => 'all']) }}" class="flex min-h-11 items-center justify-between rounded-xl bg-zinc-50 px-4 text-sm font-semibold text-zinc-800 transition hover:bg-emerald-50 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                            All business profiles <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('admin.categories') }}" class="flex min-h-11 items-center justify-between rounded-xl bg-zinc-50 px-4 text-sm font-semibold text-zinc-800 transition hover:bg-emerald-50 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                            Business categories <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('admin.locations') }}" class="flex min-h-11 items-center justify-between rounded-xl bg-zinc-50 px-4 text-sm font-semibold text-zinc-800 transition hover:bg-emerald-50 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                            LGAs and towns <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex min-h-11 items-center justify-between rounded-xl bg-zinc-50 px-4 text-sm font-semibold text-zinc-800 transition hover:bg-emerald-50 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                            Manage accounts <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('admin.reviews.index') }}" class="flex min-h-11 items-center justify-between rounded-xl bg-zinc-50 px-4 text-sm font-semibold text-zinc-800 transition hover:bg-emerald-50 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                            Moderate reviews <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('admin.business-reports.index') }}" class="flex min-h-11 items-center justify-between rounded-xl bg-zinc-50 px-4 text-sm font-semibold text-zinc-800 transition hover:bg-emerald-50 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                            Review listing reports <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/70 dark:bg-amber-950/30 sm:p-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-amber-900 dark:text-amber-200">Review standard</p>
                    <p class="mt-2 text-sm leading-6 text-amber-950/80 dark:text-amber-100/80">Approval publishes a listing to the directory. Rejection requires a clear reason so the owner can correct and resubmit it.</p>
                </div>
            </aside>
        </section>
    </main>
</x-layouts::app>

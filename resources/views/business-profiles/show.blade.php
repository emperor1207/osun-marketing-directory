<x-layouts::app :title="__('Business profile')">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex flex-col gap-3">
                <p class="text-sm font-semibold uppercase tracking-wide text-green-700 dark:text-green-400">Business owner</p>
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $businessProfile->business_name }}</h1>
                <p class="text-zinc-600 dark:text-zinc-400">Your business profile and approval status.</p>
            </div>
            <a href="{{ route('business-profile.edit') }}" class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Edit profile</a>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/40 dark:text-green-200">
                {{ session('status') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @if ($businessProfile->cover_image_url)
                <img src="{{ $businessProfile->cover_image_url }}" alt="" class="h-48 w-full object-cover sm:h-64">
            @endif

            <div class="flex flex-col gap-6 p-5 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    @if ($businessProfile->logo_url)
                        <img src="{{ $businessProfile->logo_url }}" alt="" class="size-20 rounded-xl border border-zinc-200 object-cover dark:border-zinc-700">
                    @endif
                    <div class="flex flex-col gap-1">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $businessProfile->business_name }}</h2>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $businessProfile->category->name }}</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-sm font-semibold capitalize
                        {{ $businessProfile->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' : '' }}
                        {{ $businessProfile->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' : '' }}
                        {{ $businessProfile->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' : '' }}">
                        {{ str_replace('_', ' ', $businessProfile->status) }}
                    </span>
                </div>

                @if ($businessProfile->status === 'pending')
                    <p class="rounded-lg bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-950/40 dark:text-amber-200">Your profile is awaiting administrator approval. You cannot change its approval status.</p>
                @elseif ($businessProfile->status === 'rejected' && $businessProfile->rejection_reason)
                    <p class="rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-950/40 dark:text-red-200"><span class="font-semibold">Review feedback:</span> {{ $businessProfile->rejection_reason }}</p>
                @endif

                @if ($businessProfile->description)
                    <div class="flex flex-col gap-2">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">About this business</h3>
                        <p class="whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $businessProfile->description }}</p>
                    </div>
                @endif

                <dl class="grid gap-5 border-t border-zinc-200 pt-6 sm:grid-cols-2 dark:border-zinc-700">
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Location</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                            {{ $businessProfile->street_address ? $businessProfile->street_address.', ' : '' }}{{ $businessProfile->town->name }}, {{ $businessProfile->town->localGovernmentArea->name }}, Osun State
                        </dd>
                    </div>
                    @foreach ([
                        'Phone' => $businessProfile->phone,
                        'WhatsApp' => $businessProfile->whatsapp,
                        'Email' => $businessProfile->email,
                        'Website' => $businessProfile->website,
                    ] as $label => $value)
                        @if ($value)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</dt>
                                <dd class="mt-1 break-words text-sm text-zinc-900 dark:text-white">
                                    @if ($label === 'Website')
                                        <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="text-green-700 underline dark:text-green-400">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        </section>

        <section class="flex flex-col gap-5" aria-labelledby="insights-heading">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Performance</p>
                <h2 id="insights-heading" class="mt-1 text-2xl font-bold text-zinc-950 dark:text-white">Business insights</h2>
            </div>

            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    'Visitors (all time)' => number_format($analytics['total_visitors']),
                    'New visitors, last 7 days' => number_format($analytics['last_7_days']),
                    'New visitors, last 30 days' => number_format($analytics['last_30_days']),
                    'Average rating' => $analytics['reviews_count'] > 0 ? number_format((float) $analytics['average_rating'], 1).' / 5' : 'No ratings yet',
                    'Reviews' => number_format($analytics['reviews_count']),
                    'Saved by customers' => number_format($analytics['saved_by']),
                    'Available listings' => $analytics['available_listings'].' of '.$analytics['listings'],
                ] as $label => $value)
                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</dt>
                        <dd class="mt-2 text-3xl font-bold text-zinc-950 dark:text-white">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            @php($maxDaily = max(1, collect($analytics['daily'])->max('count')))
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="font-semibold text-zinc-950 dark:text-white">New visitors, last 14 days</h3>
                <div class="mt-4 flex h-32 items-end gap-1.5" role="img" aria-label="Bar chart of new visitors for each of the last 14 days">
                    @foreach ($analytics['daily'] as $day)
                        <div class="flex h-full flex-1 items-end">
                            <div class="w-full rounded-t bg-emerald-600" style="height: {{ max(4, round($day['count'] / $maxDaily * 100)) }}%" title="{{ $day['label'] }}: {{ $day['count'] }}"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                    <span>{{ $analytics['daily'][0]['label'] }}</span>
                    <span>{{ $analytics['daily'][13]['label'] }}</span>
                </div>
                <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">Each visitor is counted once, on their first visit. Your own visits are not counted.</p>
            </div>
        </section>
    </main>
</x-layouts::app>

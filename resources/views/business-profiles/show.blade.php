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
    </main>
</x-layouts::app>

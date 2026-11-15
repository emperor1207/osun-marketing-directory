<x-layouts::app :title="__('Review business profile')">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <a href="{{ route('admin.profiles.index', ['status' => $businessProfile->status]) }}" class="w-fit text-sm font-semibold text-green-800 hover:underline dark:text-green-300">Back to profiles</a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $businessProfile->business_name }}</h1>
                <span class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-semibold capitalize text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ $businessProfile->status }}</span>
            </div>
            <p class="text-zinc-600 dark:text-zinc-400">Submitted by {{ $businessProfile->owner->name }} ({{ $businessProfile->owner->email }})</p>
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

        <article class="flex flex-col gap-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-8">
            @if ($businessProfile->cover_image_url)
                <img src="{{ $businessProfile->cover_image_url }}" alt="" class="h-52 w-full rounded-xl object-cover">
            @endif
            <div class="flex items-start gap-4">
                @if ($businessProfile->logo_url)
                    <img src="{{ $businessProfile->logo_url }}" alt="" class="size-20 rounded-xl border border-zinc-200 object-cover dark:border-zinc-700">
                @endif
                <div class="flex flex-col gap-1">
                    <p class="font-semibold text-green-800 dark:text-green-300">{{ $businessProfile->category->name }}</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $businessProfile->town->name }}, {{ $businessProfile->town->localGovernmentArea->name }}</p>
                </div>
            </div>
            @if ($businessProfile->description)
                <p class="whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-300">{{ $businessProfile->description }}</p>
            @endif
            <dl class="grid gap-4 border-t border-zinc-200 pt-5 sm:grid-cols-2 dark:border-zinc-700">
                @foreach ([
                    'Address' => $businessProfile->street_address,
                    'Phone' => $businessProfile->phone,
                    'WhatsApp' => $businessProfile->whatsapp,
                    'Email' => $businessProfile->email,
                    'Website' => $businessProfile->website,
                ] as $label => $value)
                    @if ($value)
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</dt>
                            <dd class="mt-1 break-words text-sm text-zinc-900 dark:text-white">{{ $value }}</dd>
                        </div>
                    @endif
                @endforeach
                @if ($businessProfile->approved_at)
                    <div>
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Approved at</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $businessProfile->approved_at->format('M j, Y g:i A') }}</dd>
                    </div>
                @endif
                @if ($businessProfile->rejection_reason)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Rejection reason</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-zinc-900 dark:text-white">{{ $businessProfile->rejection_reason }}</dd>
                    </div>
                @endif
            </dl>
        </article>

        @include('business-products._item-grid', ['products' => $businessProfile->products])

        @if ($businessProfile->status === 'pending')
            <section class="grid gap-5 md:grid-cols-2" aria-label="Profile decision">
                <form method="POST" action="{{ route('admin.profiles.approve', $businessProfile) }}" class="flex flex-col gap-4 rounded-2xl border border-green-200 bg-green-50 p-5 dark:border-green-900 dark:bg-green-950/30">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <h2 class="font-semibold text-green-900 dark:text-green-200">Approve listing</h2>
                        <p class="text-sm text-green-800 dark:text-green-300">The business will become visible in the verified directory.</p>
                    </div>
                    <button type="submit" class="inline-flex w-fit rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">Approve profile</button>
                </form>

                <form method="POST" action="{{ route('admin.profiles.reject', $businessProfile) }}" class="flex flex-col gap-4 rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-900 dark:bg-red-950/30">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <h2 class="font-semibold text-red-900 dark:text-red-200">Reject listing</h2>
                        <p class="text-sm text-red-800 dark:text-red-300">Provide clear feedback so the owner knows what to correct.</p>
                    </div>
                    <div>
                        <label for="rejection_reason" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Reason for rejection</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" maxlength="2000" required class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-red-700 focus:ring-2 focus:ring-red-700/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="inline-flex w-fit rounded-lg bg-red-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-800">Reject profile</button>
                </form>
            </section>
        @endif
    </main>
</x-layouts::app>

<x-layouts::app :title="__('Moderate review')">
    <main class="mx-auto flex w-full max-w-4xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <a href="{{ route('admin.reviews.index') }}" class="w-fit text-sm font-semibold text-emerald-800 hover:underline dark:text-emerald-300">Back to review moderation</a>
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Administration</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Review details</h1>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif

        <article class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-zinc-950 dark:text-white">{{ $review->businessProfile->business_name }}</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $review->customer->name }} · {{ $review->customer->email }}</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ $review->rating }} / 5 stars</span>
            </div>
            <p class="whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-300">{{ $review->content }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Submitted {{ $review->created_at->format('M j, Y g:i A') }} · Status: {{ ucfirst($review->status) }}</p>
            @if ($review->moderated_at)
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Last moderated {{ $review->moderated_at->format('M j, Y g:i A') }}.</p>
            @endif
            <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $review->status === 'visible' ? 'hidden' : 'visible' }}">
                    <button class="inline-flex min-h-10 items-center rounded-lg bg-emerald-800 px-4 text-sm font-semibold text-white">{{ $review->status === 'visible' ? 'Hide review' : 'Restore review' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Permanently delete this review and its response?')">
                    @csrf
                    @method('DELETE')
                    <button class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-4 text-sm font-semibold text-red-700 dark:border-red-900 dark:text-red-300">Delete review</button>
                </form>
            </div>
        </article>

        <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
            <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Business response</h2>
            @if ($review->reply)
                <div class="mt-4 flex flex-col gap-4">
                    <div>
                        <p class="font-semibold text-zinc-900 dark:text-white">{{ $review->reply->businessOwner->name }}</p>
                        <p class="mt-2 whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-300">{{ $review->reply->content }}</p>
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Status: {{ ucfirst($review->reply->status) }}@if ($review->reply->moderated_at) · Moderated {{ $review->reply->moderated_at->format('M j, Y g:i A') }}@endif</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('admin.review-replies.update', $review->reply) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $review->reply->status === 'visible' ? 'hidden' : 'visible' }}">
                            <button class="inline-flex min-h-10 items-center rounded-lg bg-emerald-800 px-4 text-sm font-semibold text-white">{{ $review->reply->status === 'visible' ? 'Hide response' : 'Restore response' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.review-replies.destroy', $review->reply) }}" onsubmit="return confirm('Permanently delete this response?')">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-4 text-sm font-semibold text-red-700 dark:border-red-900 dark:text-red-300">Delete response</button>
                        </form>
                    </div>
                </div>
            @else
                <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">No business response has been submitted.</p>
            @endif
        </section>
    </main>
</x-layouts::app>

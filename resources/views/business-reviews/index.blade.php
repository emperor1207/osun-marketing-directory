<x-layouts::app :title="__('Customer reviews')">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Business owner</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Customer reviews</h1>
            <p class="leading-7 text-zinc-600 dark:text-zinc-300">Read feedback for {{ $businessProfile->business_name }} and respond to customers.</p>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif

        @if ($reviews->isEmpty())
            <section class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">No customer reviews yet</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Customer feedback will appear here after it is submitted.</p>
            </section>
        @else
            <section class="flex flex-col gap-5">
                @foreach ($reviews as $review)
                    <article class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                        <header class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="font-semibold text-zinc-950 dark:text-white">{{ $review->customer->name }}</h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $review->created_at->format('M j, Y') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-amber-700 dark:text-amber-300">{{ $review->rating }} / 5 <span aria-hidden="true">★</span></span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $review->status === 'visible' ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200' }}">{{ ucfirst($review->status) }}</span>
                            </div>
                        </header>
                        <p class="whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-300">{{ $review->content }}</p>

                        @if ($review->reply?->status === 'hidden')
                            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-900 dark:bg-amber-950/40 dark:text-amber-200">Your response is hidden by an administrator. Editing it will not restore its visibility.</p>
                        @endif
                        <form method="POST" action="{{ route('business-reviews.reply', $review) }}" class="flex flex-col gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            @csrf
                            @method('PUT')
                            <label for="reply-{{ $review->id }}" class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $review->reply ? 'Edit your response' : 'Respond to this review' }}</label>
                            <textarea id="reply-{{ $review->id }}" name="content" rows="3" maxlength="5000" required class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('content', $review->reply?->content) }}</textarea>
                            @error('content') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-emerald-800 px-4 text-sm font-semibold text-white hover:bg-emerald-900">{{ $review->reply ? 'Save response' : 'Post response' }}</button>
                                @if ($review->reply)
                                    <button form="delete-reply-{{ $review->id }}" type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-red-200 px-4 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300">Delete response</button>
                                @endif
                            </div>
                        </form>
                        @if ($review->reply)
                            <form id="delete-reply-{{ $review->id }}" method="POST" action="{{ route('business-reviews.reply.destroy', $review) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </article>
                @endforeach
                {{ $reviews->links() }}
            </section>
        @endif
    </main>
</x-layouts::app>

<x-layouts::app :title="$businessProfile->business_name">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('directory.index') }}" class="inline-flex w-fit items-center gap-2 text-sm font-semibold text-green-800 hover:text-green-900 dark:text-green-300 dark:hover:text-green-200">
            <span aria-hidden="true">&larr;</span>
            Back to directory
        </a>

        <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @if ($coverImageUrl)
                <img src="{{ $coverImageUrl }}" alt="{{ $businessProfile->business_name }} cover" class="h-48 w-full object-cover sm:h-72">
            @endif

            <div class="flex flex-col gap-8 p-5 sm:p-8">
                <header class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $businessProfile->business_name }} logo" class="size-24 shrink-0 rounded-2xl border border-zinc-200 object-cover dark:border-zinc-700">
                    @endif
                    <div class="flex min-w-0 flex-col gap-2">
                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">{{ $businessProfile->category->name }}</p>
                        <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">{{ $businessProfile->business_name }}</h1>
                        <p class="text-zinc-600 dark:text-zinc-400">{{ $businessProfile->town->name }}, {{ $businessProfile->town->localGovernmentArea->name }}, Osun State</p>
                        <p class="font-medium text-amber-700 dark:text-amber-300">
                            @if ($businessProfile->visible_reviews_count > 0)
                                {{ number_format((float) $businessProfile->visible_reviews_avg_rating, 1) }} / 5
                                <span aria-hidden="true">★</span>
                                · {{ number_format($businessProfile->visible_reviews_count) }} {{ \Illuminate\Support\Str::plural('review', $businessProfile->visible_reviews_count) }}
                            @else
                                No customer ratings yet
                            @endif
                        </p>
                    </div>
                </header>

                <div class="flex flex-wrap gap-3" role="group" aria-label="Contact {{ $businessProfile->business_name }}">
                    @if ($phoneUrl)
                        <a href="{{ $phoneUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 text-sm font-semibold text-white transition hover:bg-emerald-900">Call now</a>
                    @endif
                    @if ($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-green-600 px-5 text-sm font-semibold text-white transition hover:bg-green-700">Chat on WhatsApp</a>
                    @endif
                    <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-zinc-300 px-5 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800">Get directions</a>
                    @if (auth()->id() !== $businessProfile->user_id)
                        <form method="POST" action="{{ route('favourites.toggle', $businessProfile) }}">
                            @csrf
                            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-rose-200 px-5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950/40">{{ $isFavourite ? '♥ Saved' : '♡ Save' }}</button>
                        </form>
                    @endif
                </div>

                @if ($businessProfile->description)
                    <section class="flex flex-col gap-3" aria-labelledby="business-description-heading">
                        <h2 id="business-description-heading" class="text-xl font-semibold text-zinc-900 dark:text-white">About this business</h2>
                        <p class="whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-300">{{ $businessProfile->description }}</p>
                    </section>
                @endif

                <section class="flex flex-col gap-4 border-t border-zinc-200 pt-6 dark:border-zinc-700" aria-labelledby="business-location-heading">
                    <h2 id="business-location-heading" class="text-xl font-semibold text-zinc-900 dark:text-white">Location and contact</h2>
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</dt>
                            <dd class="mt-1 text-zinc-900 dark:text-white">
                                {{ $businessProfile->street_address ? $businessProfile->street_address.', ' : '' }}{{ $businessProfile->town->name }}, {{ $businessProfile->town->localGovernmentArea->name }}, Osun State
                            </dd>
                        </div>

                        @if ($businessProfile->phone)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone</dt>
                                <dd class="mt-1"><a href="tel:{{ $businessProfile->phone }}" class="break-words text-green-800 underline dark:text-green-300">{{ $businessProfile->phone }}</a></dd>
                            </div>
                        @endif

                        @if ($businessProfile->whatsapp && $whatsappUrl)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">WhatsApp</dt>
                                <dd class="mt-1"><a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="break-words text-green-800 underline dark:text-green-300">{{ $businessProfile->whatsapp }}</a></dd>
                            </div>
                        @endif

                        @if ($businessProfile->email)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</dt>
                                <dd class="mt-1"><a href="mailto:{{ $businessProfile->email }}" class="break-words text-green-800 underline dark:text-green-300">{{ $businessProfile->email }}</a></dd>
                            </div>
                        @endif

                        @if ($businessProfile->website)
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Website</dt>
                                <dd class="mt-1"><a href="{{ $businessProfile->website }}" target="_blank" rel="noopener noreferrer" class="break-words text-green-800 underline dark:text-green-300">{{ $businessProfile->website }}</a></dd>
                            </div>
                        @endif
                    </dl>

                    @if ($mapEmbedUrl)
                        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <iframe src="{{ $mapEmbedUrl }}" title="Map showing {{ $businessProfile->business_name }}" class="h-64 w-full sm:h-80" loading="lazy" referrerpolicy="no-referrer"></iframe>
                        </div>
                        <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="w-fit text-sm font-semibold text-green-800 underline dark:text-green-300">View larger map</a>
                    @endif
                </section>
            </div>
        </article>

        @include('business-products._item-grid', ['products' => $businessProfile->products])

        <section class="flex flex-col gap-6" aria-labelledby="business-reviews-heading">
            <header class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Customer feedback</p>
                    <h2 id="business-reviews-heading" class="mt-1 text-2xl font-bold text-zinc-950 dark:text-white">Reviews &amp; ratings</h2>
                </div>
                <p class="font-semibold text-amber-700 dark:text-amber-300">
                    {{ $businessProfile->visible_reviews_count > 0 ? number_format((float) $businessProfile->visible_reviews_avg_rating, 1).' / 5' : 'Not rated yet' }}
                </p>
            </header>

            @if (session('status'))
                <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
            @endif

            @if ($canReview || $myReview)
                <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-6">
                    <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $myReview ? 'Your review' : 'Share your experience' }}</h3>
                    @if ($myReview?->status === 'hidden')
                        <p class="mt-2 text-sm text-amber-800 dark:text-amber-300">Your review is hidden while it is being moderated. Editing it will not restore its public visibility.</p>
                    @endif
                    <form
                        method="POST"
                        action="{{ $myReview ? route('reviews.update', $myReview) : route('reviews.store', $businessProfile) }}"
                        class="mt-4 flex flex-col gap-4"
                    >
                        @csrf
                        @if ($myReview)
                            @method('PUT')
                        @endif
                        <div>
                            <label for="rating" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Rating</label>
                            <select id="rating" name="rating" required class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white sm:max-w-xs">
                                @foreach (range(5, 1) as $rating)
                                    <option value="{{ $rating }}" @selected((int) old('rating', $myReview?->rating ?? 5) === $rating)>{{ $rating }} {{ \Illuminate\Support\Str::plural('star', $rating) }}</option>
                                @endforeach
                            </select>
                            @error('rating') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="review-content" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Your feedback</label>
                            <textarea id="review-content" name="content" rows="4" maxlength="5000" required class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('content', $myReview?->content) }}</textarea>
                            @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 font-semibold text-white hover:bg-emerald-900">{{ $myReview ? 'Update review' : 'Submit review' }}</button>
                            @if ($myReview)
                                <button form="delete-review-{{ $myReview->id }}" type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-red-200 px-5 font-semibold text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950/40">Delete review</button>
                            @endif
                        </div>
                    </form>
                    @if ($myReview)
                        <form id="delete-review-{{ $myReview->id }}" method="POST" action="{{ route('reviews.destroy', $myReview) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </article>
            @elseif (! auth()->user()->isCustomer())
                <p class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">Customer reviews are available to customer accounts.</p>
            @endif

            @if ($reviews->isEmpty())
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="font-semibold text-zinc-950 dark:text-white">No reviews yet</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Be the first customer to share feedback about this business.</p>
                </div>
            @else
                <div class="grid gap-4">
                    @foreach ($reviews as $review)
                        <article class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                            <header class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-zinc-950 dark:text-white">{{ $review->customer->name }}</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $review->created_at->format('M j, Y') }}</p>
                                </div>
                                <p class="font-semibold text-amber-700 dark:text-amber-300" aria-label="Rated {{ $review->rating }} out of 5">
                                    {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                </p>
                            </header>
                            <p class="whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-300">{{ $review->content }}</p>
                            @if ($review->reply)
                                <div class="rounded-xl border-s-4 border-emerald-600 bg-emerald-50 p-4 dark:bg-emerald-950/30">
                                    <p class="text-sm font-semibold text-emerald-950 dark:text-emerald-200">Business response</p>
                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $review->reply->content }}</p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
                {{ $reviews->links() }}
            @endif
        </section>

        @if ($canReport)
            <details class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <summary class="cursor-pointer font-semibold text-zinc-950 dark:text-white">Report inaccurate or inappropriate listing</summary>
                <form method="POST" action="{{ route('business-reports.store', $businessProfile) }}" class="mt-5 flex flex-col gap-4">
                    @csrf
                    <div>
                        <label for="report-reason" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Reason</label>
                        <select id="report-reason" name="reason" required class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            <option value="">Select a reason</option>
                            <option value="incorrect_information" @selected(old('reason') === 'incorrect_information')>Incorrect business information</option>
                            <option value="misleading_listing" @selected(old('reason') === 'misleading_listing')>Misleading listing</option>
                            <option value="inappropriate_content" @selected(old('reason') === 'inappropriate_content')>Inappropriate content</option>
                            <option value="duplicate_listing" @selected(old('reason') === 'duplicate_listing')>Duplicate listing</option>
                            <option value="closed_business" @selected(old('reason') === 'closed_business')>Business no longer operating</option>
                            <option value="other" @selected(old('reason') === 'other')>Other</option>
                        </select>
                        @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="report-description" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Details <span class="font-normal text-zinc-500">(optional, required for “Other”)</span></label>
                        <textarea id="report-description" name="description" rows="3" maxlength="2000" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="inline-flex min-h-11 w-fit items-center justify-center rounded-xl border border-amber-300 px-5 font-semibold text-amber-900 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-200 dark:hover:bg-amber-950/40">Submit report</button>
                </form>
            </details>
        @elseif (auth()->user()->isCustomer())
            <p class="text-sm text-zinc-600 dark:text-zinc-400">You have already submitted a report for this business. <a href="{{ route('business-reports.mine') }}" class="font-semibold text-emerald-800 underline dark:text-emerald-300">View your reports</a></p>
        @endif
    </main>
</x-layouts::app>

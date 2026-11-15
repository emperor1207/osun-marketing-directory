<x-layouts::app :title="__('Review listing report')">
    <main class="mx-auto flex w-full max-w-4xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <a href="{{ route('admin.business-reports.index') }}" class="w-fit text-sm font-semibold text-emerald-800 hover:underline dark:text-emerald-300">Back to reports</a>
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Administration</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Report details</h1>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif

        <article class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-zinc-950 dark:text-white">{{ $report->businessProfile->business_name }}</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Reported by {{ $report->customer->name }} ({{ $report->customer->email }})</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold capitalize text-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ str($report->status)->replace('_', ' ') }}</span>
            </div>
            <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ str($report->reason)->replace('_', ' ')->title() }}</p>
            <p class="whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-300">{{ $report->description ?: 'No additional details were provided.' }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Submitted {{ $report->created_at->format('M j, Y g:i A') }}</p>
            @if ($report->resolution_note)
                <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Previous resolution note</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $report->resolution_note }}</p>
                    @if ($report->handler)
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">Updated by {{ $report->handler->name }}</p>
                    @endif
                </div>
            @endif
            <a href="{{ route('admin.profiles.show', $report->businessProfile) }}" class="w-fit text-sm font-semibold text-emerald-800 underline dark:text-emerald-300">Inspect business listing</a>
        </article>

        <form method="POST" action="{{ route('admin.business-reports.update', $report) }}" class="flex flex-col gap-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
            @csrf
            @method('PATCH')
            <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Update report status</h2>
            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Status</label>
                <select id="status" name="status" required class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white sm:max-w-sm">
                    @foreach (['under_review' => 'Under review', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $report->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="resolution-note" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Resolution note <span class="font-normal text-zinc-500">(required when resolving or dismissing)</span></label>
                <textarea id="resolution-note" name="resolution_note" rows="4" maxlength="2000" class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('resolution_note', $report->resolution_note) }}</textarea>
                @error('resolution_note') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="inline-flex min-h-11 w-fit items-center justify-center rounded-xl bg-emerald-800 px-5 font-semibold text-white">Save report update</button>
        </form>
    </main>
</x-layouts::app>

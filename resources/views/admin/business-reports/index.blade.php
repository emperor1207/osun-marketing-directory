<x-layouts::app :title="__('Business listing reports')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Administration</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Business listing reports</h1>
            <p class="leading-7 text-zinc-600 dark:text-zinc-300">Review customer reports and record the resolution or next steps.</p>
        </header>

        <form method="GET" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 md:grid-cols-[1fr_auto_auto] md:items-end">
            <div>
                <label for="report-search" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Search report, customer, or business</label>
                <input id="report-search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
            </div>
            <div>
                <label for="report-status" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Status</label>
                <select id="report-status" name="status" class="min-h-11 rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @foreach (['all' => 'All reports', 'submitted' => 'Submitted', 'under_review' => 'Under review', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 font-semibold text-white">Filter reports</button>
        </form>

        @if ($reports->isEmpty())
            <section class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">No reports match these filters</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Customer-submitted reports will appear here.</p>
            </section>
        @else
            <section class="flex flex-col gap-4">
                @foreach ($reports as $report)
                    <article class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex min-w-0 flex-col gap-2">
                            <a href="{{ route('admin.business-reports.show', $report) }}" class="font-semibold text-emerald-800 hover:underline dark:text-emerald-300">{{ $report->businessProfile->business_name }}</a>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $report->customer->name }} · {{ $report->customer->email }}</p>
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ str($report->reason)->replace('_', ' ')->title() }}</p>
                            @if ($report->description)
                                <p class="whitespace-pre-line text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ \Illuminate\Support\Str::limit($report->description, 220) }}</p>
                            @endif
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Submitted {{ $report->created_at->format('M j, Y') }}</p>
                        </div>
                        <span class="w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold capitalize text-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ str($report->status)->replace('_', ' ') }}</span>
                    </article>
                @endforeach
                {{ $reports->links() }}
            </section>
        @endif
    </main>
</x-layouts::app>

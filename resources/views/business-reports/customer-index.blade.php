<x-layouts::app :title="__('My listing reports')">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Customer dashboard</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">My listing reports</h1>
            <p class="leading-7 text-zinc-600 dark:text-zinc-300">See the status of business listing concerns you have submitted.</p>
        </header>

        @if ($reports->isEmpty())
            <section class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">You have not submitted any reports</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">If you find a listing that needs attention, open its business detail page to report it.</p>
                <a href="{{ route('directory.index') }}" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-800 px-4 text-sm font-semibold text-white">Browse directory</a>
            </section>
        @else
            <div class="flex flex-col gap-4">
                @foreach ($reports as $report)
                    <article class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex flex-col gap-2">
                            <h2 class="font-semibold text-zinc-950 dark:text-white">{{ $report->businessProfile->business_name }}</h2>
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ str($report->reason)->replace('_', ' ')->title() }}</p>
                            @if ($report->description)
                                <p class="whitespace-pre-line text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $report->description }}</p>
                            @endif
                            @if ($report->resolution_note)
                                <p class="rounded-xl bg-zinc-50 p-3 text-sm text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"><span class="font-semibold">Administrator update:</span> {{ $report->resolution_note }}</p>
                            @endif
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Submitted {{ $report->created_at->format('M j, Y') }}</p>
                        </div>
                        <span class="w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold capitalize text-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ str($report->status)->replace('_', ' ') }}</span>
                    </article>
                @endforeach
                {{ $reports->links() }}
            </div>
        @endif
    </main>
</x-layouts::app>

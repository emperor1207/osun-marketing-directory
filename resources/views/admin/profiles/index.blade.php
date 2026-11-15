<x-layouts::app :title="__('Business profile reviews')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <a href="{{ route('admin.dashboard') }}" class="w-fit text-sm font-semibold text-green-800 hover:underline dark:text-green-300">Administration</a>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">Business profiles</h1>
            <p class="text-zinc-600 dark:text-zinc-400">Review pending submissions and browse past decisions.</p>
        </header>

        <nav class="flex flex-wrap gap-2" aria-label="Filter profiles by approval status">
            @foreach (['all' => 'All profiles', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                <a href="{{ route('admin.profiles.index', ['status' => $value]) }}" @class([
                    'rounded-full px-4 py-2 text-sm font-semibold',
                    'bg-green-700 text-white' => $status === $value,
                    'bg-white text-zinc-700 ring-1 ring-zinc-300 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-800' => $status !== $value,
                ])>{{ $label }}</a>
            @endforeach
        </nav>

        @if ($profiles->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-5 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <p class="font-medium text-zinc-900 dark:text-white">No profiles in this view.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <table class="w-full min-w-[760px] divide-y divide-zinc-200 text-left dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-5 py-4">Business</th>
                            <th class="px-5 py-4">Owner</th>
                            <th class="px-5 py-4">Location</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4"><span class="sr-only">Action</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($profiles as $businessProfile)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $businessProfile->business_name }}</p>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $businessProfile->category->name }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm text-zinc-900 dark:text-white">{{ $businessProfile->owner->name }}</p>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $businessProfile->owner->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">{{ $businessProfile->town->name }}, {{ $businessProfile->town->localGovernmentArea->name }}</td>
                                <td class="px-5 py-4">
                                    <span @class([
                                        'rounded-full px-3 py-1 text-xs font-semibold capitalize',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' => $businessProfile->status === 'pending',
                                        'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200' => $businessProfile->status === 'approved',
                                        'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200' => $businessProfile->status === 'rejected',
                                    ])>{{ $businessProfile->status }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.profiles.show', $businessProfile) }}" class="text-sm font-semibold text-green-800 hover:underline dark:text-green-300">Review</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $profiles->links() }}
        @endif
    </main>
</x-layouts::app>

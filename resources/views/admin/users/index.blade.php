<x-layouts::app :title="__('Account management')">
    <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Administration</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Account management</h1>
            <p class="leading-7 text-zinc-600 dark:text-zinc-300">Inspect customer and business-owner accounts, update roles, and safely deactivate access.</p>
        </header>

        <form method="GET" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 md:grid-cols-[1fr_auto_auto_auto] md:items-end">
            <div>
                <label for="account-search" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Search name or email</label>
                <input id="account-search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
            </div>
            <div>
                <label for="account-role" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Role</label>
                <select id="account-role" name="role" class="min-h-11 rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @foreach (['all' => 'All roles', 'customer' => 'Customers', 'business_owner' => 'Business owners', 'admin' => 'Administrators'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['role'] ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="account-status" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Account status</label>
                <select id="account-status" name="status" class="min-h-11 rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @foreach (['all' => 'All accounts', 'active' => 'Active', 'inactive' => 'Deactivated'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-800 px-5 font-semibold text-white">Filter accounts</button>
        </form>

        @if ($users->isEmpty())
            <section class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">No accounts match these filters</h2>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Registered accounts will appear here.</p>
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[44rem] divide-y divide-zinc-200 text-left text-sm dark:divide-zinc-800">
                        <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <tr><th class="px-5 py-4">Account</th><th class="px-5 py-4">Role</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Joined</th><th class="px-5 py-4"><span class="sr-only">Actions</span></th></tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-5 py-4"><p class="font-semibold text-zinc-950 dark:text-white">{{ $user->name }}</p><p class="mt-1 text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p></td>
                                    <td class="px-5 py-4 capitalize text-zinc-700 dark:text-zinc-300">{{ str($user->role)->replace('_', ' ') }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $user->isActive() ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">{{ $user->isActive() ? 'Active' : 'Deactivated' }}</span></td>
                                    <td class="px-5 py-4 text-zinc-600 dark:text-zinc-400">{{ $user->created_at->format('M j, Y') }}</td>
                                    <td class="px-5 py-4"><a href="{{ route('admin.users.show', $user) }}" class="font-semibold text-emerald-800 underline dark:text-emerald-300">Inspect</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-5">{{ $users->links() }}</div>
            </section>
        @endif
    </main>
</x-layouts::app>

<x-layouts::app :title="__('Account details')">
    <main class="mx-auto flex w-full max-w-4xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <a href="{{ route('admin.users.index') }}" class="w-fit text-sm font-semibold text-emerald-800 hover:underline dark:text-emerald-300">Back to accounts</a>
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">Administration</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Account details</h1>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <article class="grid gap-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:grid-cols-2 sm:p-7">
            <div><p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Name</p><p class="mt-1 font-semibold text-zinc-950 dark:text-white">{{ $user->name }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Email</p><p class="mt-1 break-words font-semibold text-zinc-950 dark:text-white">{{ $user->email }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Role</p><p class="mt-1 capitalize font-semibold text-zinc-950 dark:text-white">{{ str($user->role)->replace('_', ' ') }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Status</p><p class="mt-1 font-semibold text-zinc-950 dark:text-white">{{ $user->isActive() ? 'Active' : 'Deactivated' }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Joined</p><p class="mt-1 font-semibold text-zinc-950 dark:text-white">{{ $user->created_at->format('M j, Y g:i A') }}</p></div>
            <div><p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Submitted reviews / reports</p><p class="mt-1 font-semibold text-zinc-950 dark:text-white">{{ $user->reviews_count }} reviews · {{ $user->business_reports_count }} reports</p></div>
            @if ($user->businessProfile)
                <div class="sm:col-span-2"><p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Business profile</p><a href="{{ route('admin.profiles.show', $user->businessProfile) }}" class="mt-1 inline-block font-semibold text-emerald-800 underline dark:text-emerald-300">{{ $user->businessProfile->business_name }} ({{ $user->businessProfile->status }})</a></div>
            @endif
        </article>

        @if ($user->id === auth()->id())
            <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">Your own account cannot be changed here. This prevents accidental loss of administrative access.</p>
        @else
            <section class="grid gap-5 md:grid-cols-2">
                <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    @csrf
                    @method('PUT')
                    <h2 class="font-semibold text-zinc-950 dark:text-white">Change account role</h2>
                    <div>
                        <label for="role" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">Role</label>
                        <select id="role" name="role" required class="min-h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            @foreach (['customer' => 'Customer', 'business_owner' => 'Business owner', 'admin' => 'Administrator'] as $value => $label)
                                <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="inline-flex min-h-10 w-fit items-center justify-center rounded-lg bg-emerald-800 px-4 text-sm font-semibold text-white">Save role</button>
                </form>

                <form method="POST" action="{{ route('admin.users.status.update', $user) }}" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    @csrf
                    @method('PATCH')
                    <h2 class="font-semibold text-zinc-950 dark:text-white">Account access</h2>
                    <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-400">Deactivated accounts cannot sign in or access protected features. Their business and activity records are retained.</p>
                    <input type="hidden" name="is_active" value="{{ $user->isActive() ? '0' : '1' }}">
                    <button class="inline-flex min-h-10 w-fit items-center justify-center rounded-lg {{ $user->isActive() ? 'border border-red-200 px-4 text-sm font-semibold text-red-700 dark:border-red-900 dark:text-red-300' : 'bg-emerald-800 px-4 text-sm font-semibold text-white' }}">
                        {{ $user->isActive() ? 'Deactivate account' : 'Activate account' }}
                    </button>
                </form>
            </section>
        @endif
    </main>
</x-layouts::app>

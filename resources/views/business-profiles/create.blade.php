<x-layouts::app :title="__('Create business profile')">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-3">
            <p class="text-sm font-semibold uppercase tracking-wide text-green-700 dark:text-green-400">Business owner</p>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">Create your business profile</h1>
            <p class="max-w-2xl text-zinc-600 dark:text-zinc-400">Add your business to the Osun State marketing directory. Your profile will remain pending until an administrator reviews it.</p>
        </header>

        @include('business-profiles._form')
    </main>
</x-layouts::app>

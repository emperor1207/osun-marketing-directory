@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-svh bg-stone-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="grid min-h-svh lg:grid-cols-[minmax(0,1.05fr)_minmax(28rem,0.95fr)]">
            <aside class="relative hidden min-h-svh overflow-hidden bg-emerald-950 text-white lg:flex lg:flex-col lg:justify-between lg:p-10 xl:p-14">
                <img
                    src="{{ asset('images/auth-market-pattern.svg') }}"
                    alt=""
                    aria-hidden="true"
                    width="900"
                    height="1200"
                    class="absolute inset-0 size-full object-cover"
                >
                <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-br from-emerald-950/25 via-transparent to-emerald-950/70"></div>
                <x-app-logo href="{{ route('home') }}" class="relative z-10 rounded-xl bg-white px-2 py-1 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white" wire:navigate />

                <div class="relative z-10 flex max-w-xl flex-col gap-6">
                    <span class="w-fit rounded-full border border-emerald-200/30 bg-white/10 px-4 py-2 text-sm font-semibold text-emerald-100 backdrop-blur">
                        Discover. Connect. Grow.
                    </span>
                    <div class="flex flex-col gap-4">
                        <h1 class="text-4xl font-bold leading-tight tracking-tight xl:text-5xl">A better way to find and share local business.</h1>
                        <p class="max-w-lg text-lg leading-8 text-emerald-100/90">Connect with businesses and services across Osun State through one trusted directory.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <p class="font-semibold text-white">For customers</p>
                            <p class="mt-1 text-sm leading-6 text-emerald-100">Search by business, category and location.</p>
                        </div>
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <p class="font-semibold text-white">For business owners</p>
                            <p class="mt-1 text-sm leading-6 text-emerald-100">Create a profile and submit it for review.</p>
                        </div>
                    </div>
                </div>

                <p class="relative z-10 text-sm text-emerald-100/75">A community directory for Osun State.</p>
            </aside>

            <main class="flex min-h-svh items-center justify-center px-4 py-8 sm:px-8 lg:px-10">
                <div class="w-full max-w-lg">
                    <div class="mb-6 flex flex-col items-center gap-3 text-center lg:hidden">
                        <x-app-logo href="{{ route('home') }}" class="focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700" wire:navigate />
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Discover local businesses across Osun State.</p>
                    </div>

                    <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-xl shadow-emerald-950/5 dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs text-zinc-500 dark:text-zinc-400">
                        <a href="{{ route('home') }}" class="rounded-sm font-medium text-emerald-800 underline underline-offset-2 transition hover:text-emerald-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:text-emerald-300" wire:navigate>
                            Back to Osun Marketing Directory
                        </a>
                    </p>
                </div>
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

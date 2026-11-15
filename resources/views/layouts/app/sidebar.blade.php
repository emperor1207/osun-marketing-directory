@php($authUser = auth()->user())

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-emerald-900 bg-emerald-950 text-white dark:border-emerald-900 dark:bg-emerald-950">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="$authUser->isAdmin() ? __('Administration') : __('Discover Osun')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="magnifying-glass" :href="route('directory.index')" :current="request()->routeIs('directory.*')" wire:navigate>
                        {{ __('Business directory') }}
                    </flux:sidebar.item>

                    @if ($authUser->isAdmin())
                        <flux:sidebar.item icon="shield-check" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            {{ __('Overview') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-storefront" :href="route('admin.profiles.index', ['status' => 'pending'])" :current="request()->routeIs('admin.profiles.*')" wire:navigate>
                            {{ __('Profile reviews') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="home" :href="route('admin.categories')" :current="request()->routeIs('admin.categories*')" wire:navigate>
                            {{ __('Categories') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="magnifying-glass" :href="route('admin.locations')" :current="request()->routeIs('admin.locations')" wire:navigate>
                            {{ __('Locations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                            {{ __('Accounts') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="star" :href="route('admin.reviews.index')" :current="request()->routeIs('admin.reviews.*')" wire:navigate>
                            {{ __('Review moderation') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="flag" :href="route('admin.business-reports.index')" :current="request()->routeIs('admin.business-reports.*')" wire:navigate>
                            {{ __('Listing reports') }}
                        </flux:sidebar.item>
                    @endif

                    @if ($authUser->isBusinessOwner())
                        <flux:sidebar.item icon="building-storefront" :href="route('business-profile.show')" :current="request()->routeIs('business-profile.*')" wire:navigate>
                            {{ __('Business profile') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-storefront" :href="route('business-products.index')" :current="request()->routeIs('business-products.*')" wire:navigate>
                            {{ __('Products & Services') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chat-bubble-left-right" :href="route('business-reviews.index')" :current="request()->routeIs('business-reviews.*')" wire:navigate>
                            {{ __('Customer reviews') }}
                        </flux:sidebar.item>
                    @endif

                    @if ($authUser->isCustomer())
                        <flux:sidebar.item icon="flag" :href="route('business-reports.mine')" :current="request()->routeIs('business-reports.mine')" wire:navigate>
                            {{ __('My reports') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="$authUser->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="$authUser->name"
                                    :initials="$authUser->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ $authUser->name }}</flux:heading>
                                    <flux:text class="truncate">{{ $authUser->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

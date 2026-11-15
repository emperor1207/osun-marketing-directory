
<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">

        <x-auth-header
            :title="__('Create an account')"
            :description="__('Join the Osun Marketing Directory to discover or promote local businesses.')"
        />

        <!-- Session Status -->
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form method="POST" action="{{ route('google.redirect') }}" class="flex flex-col gap-5" data-google-registration-form>
            @csrf
            <input type="hidden" name="intent" value="register">
            <input type="hidden" name="username" value="">
            <input type="hidden" name="role" value="">
            <button
                type="submit"
                class="inline-flex min-h-11 w-full items-center justify-center gap-3 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-800 shadow-sm transition hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
            >
                <svg aria-hidden="true" viewBox="0 0 48 48" class="size-5">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94a11.05 11.05 0 0 1-4.8 7.24l7.73 6C44.38 38.05 46.98 31.89 46.98 24.55Z"/>
                    <path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.87.93 7.53 2.56 10.78l7.97-6.19Z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.8l-7.73-6c-2.14 1.44-4.89 2.3-8.17 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
                </svg>
                {{ __('Continue with Google') }}
            </button>
        </form>

        <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
            <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></span>
            <span>{{ __('or create an account with email') }}</span>
            <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></span>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5" data-registration-form>
            @csrf

            <!-- Full Name -->
            <flux:input
                name="name"
                :label="__('Full name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Enter your full name"
            />

            <!-- Username -->
            <flux:input
                name="username"
                :label="__('Username')"
                :value="old('username')"
                type="text"
                required
                autocomplete="username"
                placeholder="Choose a username"
            />
            <p class="-mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Use 3–50 letters, numbers, dashes, or underscores. Usernames are case-insensitive.') }}
            </p>
            @error('username')
                <p class="-mt-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Account Type -->
            <div>
                <label for="role" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
                    Account type
                </label>

                <select
                    name="role"
                    id="role"
                    data-registration-role
                    required
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:ring-zinc-700"
                    aria-describedby="role-description"
                >
                    <option value="customer" {{ old('role', 'customer') === 'customer' ? 'selected' : '' }}>
                        Customer – Discover local businesses
                    </option>

                    <option value="business_owner" {{ old('role') === 'business_owner' ? 'selected' : '' }}>
                        Business Owner – Promote my business
                    </option>
                </select>

                <p id="role-description" class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Select the account type that best describes how you intend to use the directory.
                </p>

                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Create a password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Re-enter your password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Submit Button -->
            <div class="pt-2">
                <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full"
                    data-test="register-user-button"
                >
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <!-- Login Link -->
        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>
                {{ __('Log in') }}
            </flux:link>
        </div>

    </div>
</x-layouts::auth>

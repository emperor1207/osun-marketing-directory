<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    private const SESSION_KEY = 'google_oauth_flow';

    public function redirect(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        if (is_string($request->input('username'))) {
            $request->merge([
                'username' => Str::lower(trim($request->input('username'))),
            ]);
        }

        $validated = $request->validate([
            'intent' => ['required', Rule::in(['login', 'register'])],
            'username' => ['exclude_unless:intent,register', 'required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'role' => ['exclude_unless:intent,register', 'required', Rule::in(['customer', 'business_owner'])],
        ]);

        $flow = [
            'intent' => $validated['intent'],
            'username' => $validated['username'] ?? null,
            'role' => $validated['role'] ?? null,
        ];

        if (! $this->googleIsConfigured()) {
            Log::error('Google OAuth is not configured.');

            return $this->redirectWithError($flow, 'username', 'Google sign-in is currently unavailable. Please try again later.');
        }

        $request->session()->put(self::SESSION_KEY, $flow);

        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $flow = $this->validatedFlow($request->session()->pull(self::SESSION_KEY));

        if ($flow === null) {
            return $this->redirectWithError(
                null,
                'username',
                'Your Google sign-in session expired. Please start again.',
            );
        }

        if ($request->query('error') !== null) {
            Log::notice('Google OAuth was declined by the user.');

            return $this->redirectWithError($flow, 'username', 'Google sign-in was not completed.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException|GuzzleException $exception) {
            Log::warning('Google OAuth callback failed.', [
                'exception_class' => $exception::class,
            ]);

            return $this->redirectWithError($flow, 'username', 'Google sign-in could not be completed. Please try again.');
        }

        $googleData = $googleUser->user;
        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();

        if (! is_array($googleData) || ($googleData['email_verified'] ?? false) !== true) {
            return $this->redirectWithError($flow, 'username', 'Google must confirm your email address before you can sign in.');
        }

        if ((! is_string($googleId) && ! is_int($googleId))
            || trim((string) $googleId) === ''
            || ! is_string($email)
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || ! is_string($name)
            || trim($name) === ''
            || Str::length($name) > 255) {
            Log::warning('Google OAuth returned incomplete account details.');

            return $this->redirectWithError($flow, 'username', 'Google did not provide the account details required to sign in.');
        }

        $googleId = trim((string) $googleId);
        $email = Str::lower(trim($email));
        $name = trim($name);

        $user = User::query()->where('google_id', $googleId)->first();
        $isNewAccount = false;

        if ($user === null) {
            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if ($user !== null) {
                if (! $user->isActive()) {
                    return $this->redirectWithError($flow, 'username', 'This account has been deactivated.');
                }

                if ($flow['intent'] === 'register' && $user->role !== $flow['role']) {
                    return $this->redirectWithError(
                        $flow,
                        'role',
                        'An account already exists with a different account type. Sign in without changing its role.',
                    );
                }

                try {
                    $user->forceFill(['google_id' => $googleId])->save();
                } catch (UniqueConstraintViolationException $exception) {
                    Log::notice('Google account could not be linked because its provider ID is already in use.', [
                        'exception_class' => $exception::class,
                    ]);

                    return $this->redirectWithError($flow, 'username', 'This Google account could not be linked. Please sign in with your existing method.');
                }
            } elseif ($flow['intent'] === 'register') {
                $isNewAccount = true;

                try {
                    $user = DB::transaction(function () use ($flow, $googleId, $email, $name): User {
                        $user = new User([
                            'name' => $name,
                            'username' => $flow['username'],
                            'email' => $email,
                            'password' => Str::random(64),
                        ]);
                        $user->forceFill([
                            'google_id' => $googleId,
                            'role' => $flow['role'],
                            'is_active' => true,
                        ]);
                        $user->save();

                        return $user;
                    });
                } catch (UniqueConstraintViolationException $exception) {
                    Log::notice('Google account registration could not satisfy a unique constraint.', [
                        'exception_class' => $exception::class,
                    ]);

                    return $this->redirectWithError(
                        $flow,
                        'username',
                        'That username or account is already in use. Choose a different username or sign in instead.',
                    );
                }
            } else {
                return $this->redirectWithError(
                    $flow,
                    'username',
                    'No account is linked to this Google profile yet. Choose Register to create an account.',
                );
            }
        }

        if (! $user->isActive()) {
            return $this->redirectWithError($flow, 'username', 'This account has been deactivated.');
        }

        if ($flow['intent'] === 'register' && ! $isNewAccount && $user->role !== $flow['role']) {
            return $this->redirectWithError(
                $flow,
                'role',
                'An account already exists with a different account type. Sign in without changing its role.',
            );
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return $isNewAccount
            ? redirect()->route('dashboard')
            : redirect()->intended(route('dashboard'));
    }

    private function googleIsConfigured(): bool
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirect = config('services.google.redirect');

        return is_string($clientId)
            && trim($clientId) !== ''
            && is_string($clientSecret)
            && trim($clientSecret) !== ''
            && is_string($redirect)
            && trim($redirect) !== '';
    }

    /**
     * @return array{intent: 'login'|'register', username: string|null, role: string|null}|null
     */
    private function validatedFlow(mixed $flow): ?array
    {
        if (! is_array($flow) || ! in_array($flow['intent'] ?? null, ['login', 'register'], true)) {
            return null;
        }

        if ($flow['intent'] === 'login') {
            return [
                'intent' => 'login',
                'username' => null,
                'role' => null,
            ];
        }

        $role = $flow['role'] ?? null;
        $username = $flow['username'] ?? null;

        if (! in_array($role, ['customer', 'business_owner'], true)
            || ! is_string($username)
            || preg_match('/\A[a-z0-9_-]{3,50}\z/', $username) !== 1) {
            return null;
        }

        return [
            'intent' => 'register',
            'username' => $username,
            'role' => $role,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $flow
     */
    private function redirectWithError(?array $flow, string $field, string $message): RedirectResponse
    {
        $destination = ($flow['intent'] ?? null) === 'register'
            ? route('register')
            : route('login');

        return redirect($destination)->withErrors([$field => $message]);
    }
}

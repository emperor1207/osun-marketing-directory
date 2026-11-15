<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Symfony\Component\Mailer\Exception\TransportException;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });
});

test('password reset mail failures show a safe message without provider details', function () {
    $user = User::factory()->create();

    Log::spy();

    Notification::shouldReceive('send')
        ->once()
        ->andThrow(new TransportException('Provider response included sensitive detail.'));

    $this->from(route('password.request'))
        ->post(route('password.request'), ['email' => $user->email])
        ->assertRedirect(route('password.request'))
        ->assertSessionHasErrors([
            'email' => 'We could not send your password reset email. Please try again later.',
        ]);

    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Password reset email delivery failed.', [
            'exception_class' => TransportException::class,
        ]);
});

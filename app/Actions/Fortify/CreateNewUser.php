<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        if (is_string($input['username'] ?? null)) {
            $input['username'] = Str::lower(trim($input['username']));
        }

        if (is_string($input['email'] ?? null)) {
            $input['email'] = Str::lower(trim($input['email']));
        }

        $validated = Validator::make($input, [
            ...$this->profileRules(),
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'password' => $this->passwordRules(),
            'role' => ['sometimes', 'required', 'in:customer,business_owner'],
        ])->validate();

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->forceFill([
            'is_active' => true,
            'role' => $validated['role'] ?? 'customer',
        ])->save();

        return $user;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:promote-admin {email : The email address of an existing account}')]
#[Description('Promote an existing account to an active administrator')]
class PromoteAdministrator extends Command
{
    public function handle(): int
    {
        $user = User::query()
            ->where('email', $this->argument('email'))
            ->first();

        if ($user === null) {
            $this->error('No account exists with that email address.');

            return self::FAILURE;
        }

        $user->forceFill([
            'role' => 'admin',
            'is_active' => true,
        ])->save();

        $this->info("{$user->email} is now an active administrator.");

        return self::SUCCESS;
    }
}

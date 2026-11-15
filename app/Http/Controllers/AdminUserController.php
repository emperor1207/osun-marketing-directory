<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['all', 'customer', 'business_owner', 'admin'])],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
        ]);

        $users = User::query()
            ->when(
                filled($filters['search'] ?? null),
                function (Builder $query) use ($filters): void {
                    $search = trim($filters['search']);
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
                }
            )
            ->when(
                ($filters['role'] ?? 'all') !== 'all',
                fn (Builder $query) => $query->where('role', $filters['role'])
            )
            ->when(
                ($filters['status'] ?? 'all') !== 'all',
                fn (Builder $query) => $query->where(
                    'is_active',
                    $filters['status'] === 'active'
                )
            )
            ->with('businessProfile:id,user_id,business_name,status')
            ->withCount(['reviews', 'businessReports'])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('filters', 'users'));
    }

    public function show(User $user): View
    {
        $user->load('businessProfile:id,user_id,business_name,status');
        $user->loadCount(['reviews', 'businessReports']);

        return view('admin.users.show', compact('user'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->id !== $request->user()->id, 403);

        $validated = $request->validate([
            'role' => ['required', Rule::in(['customer', 'business_owner', 'admin'])],
        ]);

        DB::transaction(function () use ($user, $validated): void {
            if ($user->isAdmin() && $user->isActive() && $validated['role'] !== 'admin') {
                $this->ensureAnotherActiveAdministratorExists();
            }

            $user->forceFill(['role' => $validated['role']])->save();
        });

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'Account role updated.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->id !== $request->user()->id, 403);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($user, $isActive): void {
            if ($user->isAdmin() && $user->isActive() && ! $isActive) {
                $this->ensureAnotherActiveAdministratorExists();
            }

            $user->forceFill(['is_active' => $isActive])->save();
        });

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', $isActive ? 'Account activated.' : 'Account deactivated.');
    }

    private function ensureAnotherActiveAdministratorExists(): void
    {
        $activeAdministrators = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id']);

        if ($activeAdministrators->count() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'The last active administrator cannot be deactivated or demoted.',
            ]);
        }
    }
}

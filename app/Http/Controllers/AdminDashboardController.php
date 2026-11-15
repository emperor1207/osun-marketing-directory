<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\BusinessProfileView;
use App\Models\BusinessReport;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $statistics = [
            'users' => User::query()->count(),
            'customers' => User::query()->where('role', 'customer')->count(),
            'business_owners' => User::query()->where('role', 'business_owner')->count(),
            'business_profiles' => BusinessProfile::query()->count(),
            'pending_profiles' => BusinessProfile::query()->where('status', 'pending')->count(),
            'approved_profiles' => BusinessProfile::query()->where('status', 'approved')->count(),
            'rejected_profiles' => BusinessProfile::query()->where('status', 'rejected')->count(),
            'active_customers' => User::query()->where('role', 'customer')->where('is_active', true)->count(),
            'active_business_owners' => User::query()->where('role', 'business_owner')->where('is_active', true)->count(),
            'active_administrators' => User::query()->where('role', 'admin')->where('is_active', true)->count(),
            'visible_reviews' => Review::query()->where('status', 'visible')->count(),
            'hidden_reviews' => Review::query()->where('status', 'hidden')->count(),
            'submitted_reports' => BusinessReport::query()->where('status', 'submitted')->count(),
            'reports_under_review' => BusinessReport::query()->where('status', 'under_review')->count(),
            'total_profile_views' => BusinessProfileView::query()->count(),
        ];

        $pendingProfiles = BusinessProfile::query()
            ->where('status', 'pending')
            ->with(['owner:id,name,email', 'category:id,name', 'town:id,name,local_government_area_id', 'town.localGovernmentArea:id,name'])
            ->latest('created_at')
            ->paginate(8);

        return view('admin.dashboard', [
            'statistics' => $statistics,
            'pendingProfiles' => $pendingProfiles,
        ]);
    }

    public function profiles(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['all', 'pending', 'approved', 'rejected'])],
        ]);
        $status = $filters['status'] ?? 'all';

        $profiles = BusinessProfile::query()
            ->with(['owner:id,name,email', 'category:id,name', 'town:id,name,local_government_area_id', 'town.localGovernmentArea:id,name'])
            ->when(
                $status !== 'all',
                fn ($query) => $query->where('status', $status)
            )
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.profiles.index', [
            'profiles' => $profiles,
            'status' => $status,
        ]);
    }

    public function showProfile(BusinessProfile $businessProfile): View
    {
        $businessProfile->load([
            'owner:id,name,email',
            'category:id,name',
            'town:id,name,local_government_area_id',
            'town.localGovernmentArea:id,name',
            'products' => fn ($query) => $query
                ->orderBy('item_type')
                ->orderBy('name'),
        ]);

        return view('admin.profiles.show', [
            'businessProfile' => $businessProfile,
        ]);
    }

    public function approve(BusinessProfile $businessProfile): RedirectResponse
    {
        abort_unless($businessProfile->status === 'pending', 409, 'Only pending profiles can be approved.');

        $businessProfile->forceFill([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ])->save();

        return redirect()
            ->route('admin.profiles.show', $businessProfile)
            ->with('status', 'The business profile has been approved.');
    }

    public function reject(Request $request, BusinessProfile $businessProfile): RedirectResponse
    {
        abort_unless($businessProfile->status === 'pending', 409, 'Only pending profiles can be rejected.');

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $businessProfile->forceFill([
            'status' => 'rejected',
            'approved_at' => null,
            'rejection_reason' => $validated['rejection_reason'],
        ])->save();

        return redirect()
            ->route('admin.profiles.show', $businessProfile)
            ->with('status', 'The business profile has been rejected.');
    }
}

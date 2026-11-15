<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $businessProfile = $user->isBusinessOwner()
            ? $user->businessProfile()->with(['category:id,name', 'town:id,name'])->first()
            : null;

        $approvedBusinessCount = 0;
        $categories = collect();
        $recentBusinesses = collect();
        $recentReviews = collect();
        $recentReports = collect();
        $customerReviewCount = 0;
        $customerReportCount = 0;
        $ownerReviewCount = 0;
        $ownerAverageRating = 0;
        $ownerProfileViewCount = 0;
        $ownerRecentViewCount = 0;
        $ownerRecentReviews = collect();

        if ($user->isCustomer()) {
            $approvedBusinessCount = BusinessProfile::query()
                ->where('status', 'approved')
                ->count();

            $categories = Category::query()
                ->select(['id', 'name'])
                ->where('is_active', true)
                ->whereHas(
                    'businessProfiles',
                    fn (Builder $query) => $query->where('status', 'approved')
                )
                ->withCount([
                    'businessProfiles as approved_businesses_count' => fn (Builder $query) => $query
                        ->where('status', 'approved'),
                ])
                ->orderBy('name')
                ->limit(6)
                ->get();

            $recentBusinesses = BusinessProfile::query()
                ->where('status', 'approved')
                ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
                ->with([
                    'category:id,name',
                    'town:id,name,local_government_area_id',
                    'town.localGovernmentArea:id,name',
                ])
                ->latest('created_at')
                ->latest('id')
                ->limit(3)
                ->get();

            $recentReviews = $user->reviews()
                ->with('businessProfile:id,business_name')
                ->latest('created_at')
                ->latest('id')
                ->limit(5)
                ->get();
            $recentReports = $user->businessReports()
                ->with('businessProfile:id,business_name')
                ->latest('created_at')
                ->latest('id')
                ->limit(5)
                ->get();
            $customerReviewCount = $user->reviews()->count();
            $customerReportCount = $user->businessReports()->count();
        } elseif ($user->isBusinessOwner() && $businessProfile !== null) {
            $ownerReviewCount = $businessProfile->reviews()
                ->where('status', 'visible')
                ->count();
            $ownerAverageRating = $businessProfile->reviews()
                ->where('status', 'visible')
                ->avg('rating') ?? 0;
            $ownerProfileViewCount = $businessProfile->views()->count();
            $ownerRecentViewCount = $businessProfile->views()
                ->where('viewed_at', '>=', now()->subDays(30))
                ->count();
            $ownerRecentReviews = $businessProfile->reviews()
                ->with(['customer:id,name', 'reply'])
                ->latest('created_at')
                ->latest('id')
                ->limit(3)
                ->get();
        }

        return view('dashboard', [
            'user' => $user,
            'businessProfile' => $businessProfile,
            'approvedBusinessCount' => $approvedBusinessCount,
            'categories' => $categories,
            'recentBusinesses' => $recentBusinesses,
            'recentReviews' => $recentReviews,
            'recentReports' => $recentReports,
            'customerReviewCount' => $customerReviewCount,
            'customerReportCount' => $customerReportCount,
            'ownerReviewCount' => $ownerReviewCount,
            'ownerAverageRating' => $ownerAverageRating,
            'ownerProfileViewCount' => $ownerProfileViewCount,
            'ownerRecentViewCount' => $ownerRecentViewCount,
            'ownerRecentReviews' => $ownerRecentReviews,
        ]);
    }
}

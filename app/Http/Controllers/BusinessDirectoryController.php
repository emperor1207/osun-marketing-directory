<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\BusinessProfileView;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'local_government_area_id' => [
                'nullable',
                'integer',
                Rule::exists('local_government_areas', 'id')->where('is_active', true),
            ],
            'town_id' => [
                'nullable',
                'integer',
                Rule::exists('towns', 'id')->where('is_active', true),
            ],
        ]);

        $businessProfiles = BusinessProfile::query()
            ->where('status', 'approved')
            ->with([
                'category:id,name',
                'town:id,name,local_government_area_id',
                'town.localGovernmentArea:id,name',
            ])
            ->when(
                filled($filters['search'] ?? null),
                function (Builder $query) use ($filters): void {
                    $search = mb_strtolower(trim($filters['search']));

                    $query->whereRaw('LOWER(business_name) LIKE ?', ['%'.$search.'%']);
                }
            )
            ->when(
                isset($filters['category_id']),
                fn (Builder $query) => $query->where('category_id', $filters['category_id'])
            )
            ->when(
                isset($filters['local_government_area_id']),
                fn (Builder $query) => $query->whereHas(
                    'town.localGovernmentArea',
                    fn (Builder $locationQuery) => $locationQuery->whereKey($filters['local_government_area_id'])
                )
            )
            ->when(
                isset($filters['town_id']),
                fn (Builder $query) => $query->where('town_id', $filters['town_id'])
            )
            ->orderBy('business_name')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('business-directory.index', [
            'businessProfiles' => $businessProfiles,
            'hasActiveFilters' => filled($filters['search'] ?? null)
                || isset($filters['category_id'])
                || isset($filters['local_government_area_id'])
                || isset($filters['town_id']),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'localGovernmentAreas' => LocalGovernmentArea::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'towns' => Town::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'local_government_area_id']),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, int $businessProfile): View
    {
        $profile = BusinessProfile::query()
            ->where('status', 'approved')
            ->withCount([
                'reviews as visible_reviews_count' => fn ($query) => $query->where('status', 'visible'),
            ])
            ->withAvg([
                'reviews as visible_reviews_avg_rating' => fn ($query) => $query->where('status', 'visible'),
            ], 'rating')
            ->with([
                'category:id,name',
                'town:id,name,local_government_area_id',
                'town.localGovernmentArea:id,name',
                'products' => fn (HasMany $products) => $products->getQuery()
                    ->orderBy('item_type')
                    ->orderBy('name'),
            ])
            ->findOrFail($businessProfile);
        $viewer = $request->user();

        if (! ($viewer instanceof User && ($viewer->isAdmin() || $profile->user_id === $viewer->id))) {
            $visitorKey = hash_hmac(
                'sha256',
                $request->session()->getId(),
                (string) config('app.key')
            );
            $viewedAt = now();

            BusinessProfileView::query()->insertOrIgnore([
                'business_profile_id' => $profile->id,
                'visitor_key' => $visitorKey,
                'viewed_at' => $viewedAt,
                'created_at' => $viewedAt,
                'updated_at' => $viewedAt,
            ]);
        }

        $isCustomer = $viewer instanceof User && $viewer->isCustomer();
        $myReview = $isCustomer
            ? $profile->reviews()->where('customer_id', $viewer->id)->first()
            : null;
        $hasReported = $isCustomer
            && $profile->reports()->where('customer_id', $viewer->id)->exists();
        $reviews = $profile->reviews()
            ->where('status', 'visible')
            ->with([
                'customer:id,name',
                'reply' => fn ($query) => $query
                    ->where('status', 'visible')
                    ->with('businessOwner:id,name'),
            ])
            ->latest('created_at')
            ->latest('id')
            ->paginate(10);

        return view('business-directory.show', [
            'businessProfile' => $profile,
            'reviews' => $reviews,
            'myReview' => $myReview,
            'canReview' => $isCustomer && $myReview === null,
            'canReport' => $isCustomer && ! $hasReported,
            'whatsappUrl' => $profile->whatsapp === null
                ? null
                : 'https://wa.me/'.preg_replace('/\D+/', '', $profile->whatsapp),
            'logoUrl' => $profile->logo_url,
            'coverImageUrl' => $profile->cover_image_url,
        ]);
    }
}

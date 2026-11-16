<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\BusinessProfileView;
use App\Models\Category;
use App\Models\Favourite;
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
    /** Average of the visible ratings, as SQL, so sorting works on every database. */
    private const RATING_SQL = "(select avg(reviews.rating) from reviews where reviews.business_profile_id = business_profiles.id and reviews.status = 'visible')";

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'item_type' => ['nullable', Rule::in(['product', 'service'])],
            'sort' => ['nullable', Rule::in(['name', 'rating', 'newest', 'popular'])],
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

        $terms = $this->searchTerms($filters['search'] ?? null);
        $itemType = $filters['item_type'] ?? null;
        $sort = $filters['sort'] ?? 'name';

        $businessProfiles = BusinessProfile::query()
            ->where('status', 'approved')
            ->withCount([
                'reviews as visible_reviews_count' => fn ($query) => $query->where('status', 'visible'),
                'views as unique_visitors_count',
            ])
            ->withAvg([
                'reviews as visible_reviews_avg_rating' => fn ($query) => $query->where('status', 'visible'),
            ], 'rating')
            ->with([
                'category:id,name',
                'town:id,name,local_government_area_id',
                'town.localGovernmentArea:id,name',
                // The products and services that matched, shown on each result card.
                'products' => function ($products) use ($terms, $itemType): void {
                    $products->where('is_available', true)
                        ->when($itemType !== null, fn ($query) => $query->where('item_type', $itemType))
                        ->when($terms !== [], fn ($query) => $query->where(
                            fn ($match) => $this->matchProductText($match, $terms)
                        ))
                        ->orderBy('name');
                },
            ])
            ->when(
                $terms !== [],
                function (Builder $query) use ($terms): void {
                    // Every typed word must match the business, its category,
                    // its town, or one of its available products/services.
                    foreach ($terms as $term) {
                        $like = '%'.$this->escapeLike($term).'%';

                        $query->where(function (Builder $match) use ($like): void {
                            $match->whereRaw("LOWER(business_profiles.business_name) LIKE ? ESCAPE '\\'", [$like])
                                ->orWhereRaw("LOWER(business_profiles.description) LIKE ? ESCAPE '\\'", [$like])
                                ->orWhereHas('category', fn (Builder $category) => $category
                                    ->whereRaw("LOWER(name) LIKE ? ESCAPE '\\'", [$like]))
                                ->orWhereHas('town', fn (Builder $town) => $town
                                    ->whereRaw("LOWER(name) LIKE ? ESCAPE '\\'", [$like]))
                                ->orWhereHas('products', fn (Builder $product) => $product
                                    ->where('is_available', true)
                                    ->where(fn (Builder $text) => $this->matchProductText($text, [$like], true)));
                        });
                    }
                }
            )
            ->when(
                $itemType !== null,
                fn (Builder $query) => $query->whereHas(
                    'products',
                    fn (Builder $product) => $product->where('is_available', true)->where('item_type', $itemType)
                )
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
            ->when($sort === 'rating', fn (Builder $query) => $query
                ->orderByRaw(self::RATING_SQL.' is null')
                ->orderByRaw(self::RATING_SQL.' desc')
                ->orderByDesc('visible_reviews_count'))
            ->when($sort === 'newest', fn (Builder $query) => $query->orderByDesc('created_at'))
            ->when($sort === 'popular', fn (Builder $query) => $query->orderByDesc('unique_visitors_count'))
            ->orderBy('business_name')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('business-directory.index', [
            'businessProfiles' => $businessProfiles,
            'hasActiveFilters' => filled($filters['search'] ?? null)
                || isset($filters['item_type'])
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
            'favouriteIds' => $request->user() === null
                ? []
                : Favourite::query()->where('user_id', $request->user()->id)->pluck('business_profile_id')->all(),
        ]);
    }

    /** Nigerian numbers such as 0801... become 234801... as WhatsApp links require. */
    private function whatsappUrl(BusinessProfile $profile): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $profile->whatsapp);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '234'.substr($digits, 1);
        }

        $text = rawurlencode("Hello {$profile->business_name}, I found your business on the Osun Marketing Directory.");

        return "https://wa.me/{$digits}?text={$text}";
    }

    private function directionsUrl(BusinessProfile $profile): string
    {
        if ($profile->latitude !== null && $profile->longitude !== null) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.(float) $profile->latitude.','.(float) $profile->longitude;
        }

        $address = trim(($profile->street_address ? $profile->street_address.', ' : '').$profile->town->name.', Osun State, Nigeria');

        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address);
    }

    /** @return array{0: string|null, 1: string|null} embeddable map and full-size map links */
    private function mapUrls(BusinessProfile $profile): array
    {
        if ($profile->latitude === null || $profile->longitude === null) {
            return [null, null];
        }

        $lat = (float) $profile->latitude;
        $lon = (float) $profile->longitude;
        $bbox = implode('%2C', [round($lon - 0.008, 6), round($lat - 0.005, 6), round($lon + 0.008, 6), round($lat + 0.005, 6)]);

        return [
            "https://www.openstreetmap.org/export/embed.html?bbox={$bbox}&layer=mapnik&marker={$lat}%2C{$lon}",
            "https://www.openstreetmap.org/?mlat={$lat}&mlon={$lon}#map=16/{$lat}/{$lon}",
        ];
    }

    /**
     * Split the typed search into lowercase words (maximum six).
     *
     * @return list<string>
     */
    private function searchTerms(?string $search): array
    {
        $words = preg_split('/\s+/u', mb_strtolower(trim((string) $search)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_slice($words, 0, 6);
    }

    /** Stop a typed % or _ from acting as a wildcard. */
    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    /**
     * Match product/service name or description against the given words.
     * With $alreadyLike = true the values are ready-made LIKE patterns.
     *
     * @param  list<string>  $terms
     */
    private function matchProductText(mixed $query, array $terms, bool $alreadyLike = false): void
    {
        foreach ($terms as $term) {
            $like = $alreadyLike ? $term : '%'.$this->escapeLike($term).'%';

            $query->orWhereRaw("LOWER(products.name) LIKE ? ESCAPE '\\'", [$like])
                ->orWhereRaw("LOWER(products.description) LIKE ? ESCAPE '\\'", [$like]);
        }
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
                    ->with('images')
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
            'whatsappUrl' => $this->whatsappUrl($profile),
            'phoneUrl' => $profile->phone === null || preg_replace('/[^\d+]/', '', $profile->phone) === ''
                ? null
                : 'tel:'.preg_replace('/[^\d+]/', '', $profile->phone),
            'directionsUrl' => $this->directionsUrl($profile),
            'mapEmbedUrl' => $this->mapUrls($profile)[0],
            'mapUrl' => $this->mapUrls($profile)[1],
            'isFavourite' => $viewer instanceof User
                && Favourite::query()->where('user_id', $viewer->id)->where('business_profile_id', $profile->id)->exists(),
            'logoUrl' => $profile->logo_url,
            'coverImageUrl' => $profile->cover_image_url,
        ]);
    }
}

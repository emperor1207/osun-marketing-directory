<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Favourite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavouriteController extends Controller
{
    public function index(Request $request): View
    {
        $businessProfiles = BusinessProfile::query()
            ->where('status', 'approved')
            ->whereIn('id', Favourite::query()->where('user_id', $request->user()->id)->select('business_profile_id'))
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
            ])
            ->orderBy('business_name')
            ->orderBy('id')
            ->paginate(12);

        return view('favourites.index', [
            'businessProfiles' => $businessProfiles,
        ]);
    }

    public function toggle(Request $request, int $businessProfile): RedirectResponse
    {
        $profile = BusinessProfile::query()
            ->where('status', 'approved')
            ->findOrFail($businessProfile);
        $user = $request->user();

        abort_if($profile->user_id === $user->id, 403, 'You cannot save your own business.');

        $favourite = Favourite::query()
            ->where('user_id', $user->id)
            ->where('business_profile_id', $profile->id);

        if ($favourite->exists()) {
            $favourite->delete();
            $message = $profile->business_name.' was removed from your saved businesses.';
        } else {
            Favourite::query()->firstOrCreate([
                'user_id' => $user->id,
                'business_profile_id' => $profile->id,
            ]);
            $message = $profile->business_name.' was added to your saved businesses.';
        }

        return back()->with('status', $message);
    }
}

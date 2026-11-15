<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BusinessReviewReplyController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $profile = $this->businessProfile($request);

        if ($profile === null) {
            return redirect()->route('business-profile.create');
        }

        return view('business-reviews.index', [
            'businessProfile' => $profile,
            'reviews' => $profile->reviews()
                ->with(['customer:id,name', 'reply'])
                ->latest('created_at')
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function saveReply(Request $request, Review $review): RedirectResponse
    {
        $profile = $this->businessProfile($request);

        if ($profile === null) {
            return redirect()->route('business-profile.create');
        }

        abort_unless($review->business_profile_id === $profile->id, 404);
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($profile, $review, $validated): void {
            $reply = $review->reply()->lockForUpdate()->first();

            if ($reply === null) {
                $review->reply()->create([
                    'business_owner_id' => $profile->user_id,
                    'content' => $validated['content'],
                    'status' => 'visible',
                ]);

                return;
            }

            abort_unless($reply->business_owner_id === $profile->user_id, 404);
            $reply->update(['content' => $validated['content']]);
        });

        return redirect()
            ->route('business-reviews.index')
            ->with('status', 'Your response has been saved.');
    }

    public function destroyReply(Request $request, Review $review): RedirectResponse
    {
        $profile = $this->businessProfile($request);

        if ($profile === null) {
            return redirect()->route('business-profile.create');
        }

        abort_unless($review->business_profile_id === $profile->id, 404);
        $reply = $review->reply()->firstOrFail();
        abort_unless($reply->business_owner_id === $profile->user_id, 404);

        $reply->delete();

        return redirect()
            ->route('business-reviews.index')
            ->with('status', 'Your response has been deleted.');
    }

    private function businessProfile(Request $request): ?BusinessProfile
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isBusinessOwner(), 403);

        return $user->businessProfile()->first();
    }
}

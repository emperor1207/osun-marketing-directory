<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    public function store(Request $request, int $businessProfile): RedirectResponse
    {
        $customer = $this->customer($request);
        $profile = BusinessProfile::query()
            ->where('status', 'approved')
            ->findOrFail($businessProfile);

        if ($profile->reviews()->where('customer_id', $customer->id)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['content' => 'You have already reviewed this business.']);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $review = $profile->reviews()->firstOrCreate(
            ['customer_id' => $customer->id],
            $validated
        );

        if (! $review->wasRecentlyCreated) {
            return back()
                ->withInput()
                ->withErrors(['content' => 'You have already reviewed this business.']);
        }

        return redirect()
            ->route('directory.show', $profile)
            ->with('status', 'Your review has been submitted.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $customer = $this->customer($request);
        abort_unless($review->customer_id === $customer->id, 404);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $review->update($validated);

        return redirect()
            ->route('directory.show', $review->business_profile_id)
            ->with('status', 'Your review has been updated.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $customer = $this->customer($request);
        abort_unless($review->customer_id === $customer->id, 404);

        $businessProfileId = $review->business_profile_id;
        $review->delete();

        return redirect()
            ->route('directory.show', $businessProfileId)
            ->with('status', 'Your review has been deleted.');
    }

    private function customer(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isCustomer(), 403);

        return $user;
    }
}

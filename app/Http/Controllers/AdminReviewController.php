<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', 'visible', 'hidden'])],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $reviews = Review::query()
            ->with(['customer:id,name,email', 'businessProfile:id,business_name', 'reply'])
            ->when(
                filled($filters['search'] ?? null),
                function ($query) use ($filters): void {
                    $search = trim($filters['search']);
                    $query->where(function ($query) use ($search): void {
                        $query->where('content', 'like', '%'.$search.'%')
                            ->orWhereHas('customer', fn ($users) => $users
                                ->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%'))
                            ->orWhereHas('businessProfile', fn ($profiles) => $profiles
                                ->where('business_name', 'like', '%'.$search.'%'));
                    });
                }
            )
            ->when(
                ($filters['status'] ?? 'all') !== 'all',
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                isset($filters['rating']),
                fn ($query) => $query->where('rating', $filters['rating'])
            )
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('filters', 'reviews'));
    }

    public function show(Review $review): View
    {
        $review->load([
            'customer:id,name,email',
            'businessProfile:id,business_name',
            'reply.businessOwner:id,name,email',
            'reply.moderator:id,name',
        ]);

        return view('admin.reviews.show', compact('review'));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['visible', 'hidden'])],
        ]);

        $review->forceFill([
            'status' => $validated['status'],
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ])->save();

        return redirect()
            ->route('admin.reviews.show', $review)
            ->with('status', 'Review moderation status updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('status', 'Review deleted.');
    }

    public function updateReply(Request $request, ReviewReply $reviewReply): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['visible', 'hidden'])],
        ]);

        $reviewReply->forceFill([
            'status' => $validated['status'],
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ])->save();

        return redirect()
            ->route('admin.reviews.show', $reviewReply->review_id)
            ->with('status', 'Business response moderation status updated.');
    }

    public function destroyReply(ReviewReply $reviewReply): RedirectResponse
    {
        $reviewId = $reviewReply->review_id;
        $reviewReply->delete();

        return redirect()
            ->route('admin.reviews.show', $reviewId)
            ->with('status', 'Business response deleted.');
    }
}

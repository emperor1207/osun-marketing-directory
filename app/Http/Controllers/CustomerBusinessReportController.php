<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerBusinessReportController extends Controller
{
    private const REASONS = [
        'incorrect_information',
        'misleading_listing',
        'inappropriate_content',
        'duplicate_listing',
        'closed_business',
        'other',
    ];

    public function index(Request $request): View
    {
        $customer = $this->customer($request);

        return view('business-reports.customer-index', [
            'reports' => $customer->businessReports()
                ->with('businessProfile:id,business_name')
                ->latest('created_at')
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function store(Request $request, int $businessProfile): RedirectResponse
    {
        $customer = $this->customer($request);
        $profile = BusinessProfile::query()
            ->where('status', 'approved')
            ->findOrFail($businessProfile);

        if ($profile->reports()->where('customer_id', $customer->id)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['reason' => 'You have already reported this business.']);
        }

        $validated = $request->validate([
            'reason' => ['required', Rule::in(self::REASONS)],
            'description' => [
                Rule::requiredIf($request->input('reason') === 'other'),
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $report = $profile->reports()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                ...$validated,
                'status' => 'submitted',
            ]
        );

        if (! $report->wasRecentlyCreated) {
            return back()
                ->withInput()
                ->withErrors(['reason' => 'You have already reported this business.']);
        }

        return redirect()
            ->route('directory.show', $profile)
            ->with('status', 'Your report has been submitted for administrator review.');
    }

    private function customer(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isCustomer(), 403);

        return $user;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BusinessReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminBusinessReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', 'submitted', 'under_review', 'resolved', 'dismissed'])],
        ]);

        $reports = BusinessReport::query()
            ->with([
                'customer:id,name,email',
                'businessProfile:id,business_name',
            ])
            ->when(
                filled($filters['search'] ?? null),
                function ($query) use ($filters): void {
                    $search = trim($filters['search']);
                    $query->where(function ($query) use ($search): void {
                        $query->where('description', 'like', '%'.$search.'%')
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
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.business-reports.index', compact('filters', 'reports'));
    }

    public function show(BusinessReport $businessReport): View
    {
        $businessReport->load([
            'customer:id,name,email',
            'businessProfile:id,business_name,status',
            'handler:id,name',
        ]);

        return view('admin.business-reports.show', [
            'report' => $businessReport,
        ]);
    }

    public function update(Request $request, BusinessReport $businessReport): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['under_review', 'resolved', 'dismissed'])],
            'resolution_note' => [
                Rule::requiredIf(in_array($request->input('status'), ['resolved', 'dismissed'], true)),
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $businessReport->forceFill([
            ...$validated,
            'handled_by' => $request->user()->id,
        ])->save();

        return redirect()
            ->route('admin.business-reports.show', $businessReport)
            ->with('status', 'Report status updated.');
    }
}

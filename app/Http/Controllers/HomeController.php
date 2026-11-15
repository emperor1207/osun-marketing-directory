<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
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

        $approvedBusinessCount = BusinessProfile::query()
            ->where('status', 'approved')
            ->count();

        $localGovernmentAreaCount = LocalGovernmentArea::query()
            ->where('is_active', true)
            ->count();

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

        return view('welcome', [
            'categories' => $categories,
            'approvedBusinessCount' => $approvedBusinessCount,
            'localGovernmentAreaCount' => $localGovernmentAreaCount,
            'recentBusinesses' => $recentBusinesses,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class BusinessProfileController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->businessOwner($request);
        $businessProfile = $user->businessProfile()
            ->with(['category', 'town.localGovernmentArea'])
            ->first();

        if ($businessProfile === null) {
            return redirect()->route('business-profile.create');
        }

        return view('business-profiles.show', [
            'businessProfile' => $businessProfile,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->businessOwner($request);

        if ($user->businessProfile()->exists()) {
            return redirect()->route('business-profile.edit');
        }

        return $this->formView('business-profiles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->businessOwner($request);

        abort_if($user->businessProfile()->exists(), 409, 'A business profile already exists.');

        $attributes = $this->validatedProfileAttributes($request);
        $attributes = $this->storeImages($request, $attributes);
        $attributes['slug'] = $this->uniqueSlug($attributes['business_name'], $attributes['town_id'], $user->id);
        $attributes['status'] = 'pending';

        $user->businessProfile()->create($attributes);

        return redirect()
            ->route('business-profile.show')
            ->with('status', 'Your business profile has been submitted for approval.');
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $user = $this->businessOwner($request);
        $businessProfile = $user->businessProfile()
            ->with('town.localGovernmentArea')
            ->first();

        if ($businessProfile === null) {
            return redirect()->route('business-profile.create');
        }

        return $this->formView('business-profiles.edit', $businessProfile);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->businessOwner($request);
        $businessProfile = $user->businessProfile()->firstOrFail();
        $attributes = $this->validatedProfileAttributes($request);
        $previousImages = [];
        $requiresReview = in_array($businessProfile->status, ['approved', 'rejected'], true);

        foreach (['logo', 'cover_image'] as $imageField) {
            if ($request->hasFile($imageField)) {
                if ($businessProfile->{$imageField} !== null) {
                    $previousImages[] = $businessProfile->{$imageField};
                }

                $attributes[$imageField] = $this->storeImage(
                    $request->file($imageField),
                    'business-profiles/'.$imageField
                );
            }
        }

        $businessProfile->fill($attributes);
        $businessProfile->slug = $this->uniqueSlug(
            $attributes['business_name'],
            $attributes['town_id'],
            $user->id,
            $businessProfile
        );

        if ($requiresReview) {
            $businessProfile->status = 'pending';
            $businessProfile->approved_at = null;
            $businessProfile->rejection_reason = null;
        }

        $businessProfile->save();

        if ($previousImages !== []) {
            Storage::disk(config('filesystems.media_disk'))->delete($previousImages);
        }

        return redirect()
            ->route('business-profile.show')
            ->with('status', 'Your business profile has been updated.');
    }

    private function businessOwner(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User && $user->isBusinessOwner(), 403);

        return $user;
    }

    /**
     * @param  view-string  $view
     */
    private function formView(string $view, ?BusinessProfile $businessProfile = null): View
    {
        return view($view, [
            'businessProfile' => $businessProfile,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'localGovernmentAreas' => LocalGovernmentArea::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'towns' => Town::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedLocalGovernmentAreaId' => $businessProfile?->town?->local_government_area_id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProfileAttributes(Request $request): array
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'local_government_area_id' => [
                'required',
                'integer',
                Rule::exists('local_government_areas', 'id')->where('is_active', true),
            ],
            'town_id' => [
                'required',
                'integer',
                Rule::exists('towns', 'id')->where(function (Builder $query) use ($request): void {
                    $query->where('local_government_area_id', $request->input('local_government_area_id'))
                        ->where('is_active', true);
                }),
            ],
            'street_address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        return Arr::only($validated, [
            'business_name',
            'category_id',
            'town_id',
            'street_address',
            'description',
            'phone',
            'whatsapp',
            'email',
            'website',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function storeImages(Request $request, array $attributes): array
    {
        foreach (['logo', 'cover_image'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $attributes[$imageField] = $this->storeImage(
                    $request->file($imageField),
                    'business-profiles/'.$imageField
                );
            }
        }

        return $attributes;
    }

    private function storeImage(UploadedFile $image, string $directory): string
    {
        $path = $image->storePublicly($directory, config('filesystems.media_disk'));

        if ($path === false) {
            throw new RuntimeException('The business profile image could not be stored.');
        }

        return $path;
    }

    private function uniqueSlug(
        string $businessName,
        int|string $townId,
        int $userId,
        ?BusinessProfile $businessProfile = null
    ): string {
        $baseSlug = Str::slug($businessName) ?: 'business-'.$userId;
        $slug = $baseSlug;
        $suffix = 0;

        while (
            BusinessProfile::query()
                ->where('town_id', $townId)
                ->where('slug', $slug)
                ->when(
                    $businessProfile !== null,
                    fn ($query) => $query->whereKeyNot($businessProfile->getKey())
                )
                ->exists()
        ) {
            $suffix++;
            $slug = $baseSlug.'-'.$userId.($suffix > 1 ? '-'.$suffix : '');
        }

        return $slug;
    }
}

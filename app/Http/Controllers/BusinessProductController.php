<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class BusinessProductController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $businessProfile = $this->ownerBusinessProfile($request);

        if ($businessProfile === null) {
            return redirect()->route('business-profile.create');
        }

        return view('business-products.index', [
            'businessProfile' => $businessProfile,
            'products' => $businessProfile->products()
                ->latest('updated_at')
                ->latest('id')
                ->get(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($this->ownerBusinessProfile($request) === null) {
            return redirect()->route('business-profile.create');
        }

        return view('business-products.create', [
            'product' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $businessProfile = $this->ownerBusinessProfile($request);

        if ($businessProfile === null) {
            return redirect()->route('business-profile.create');
        }

        $attributes = $this->validatedAttributes($request);
        $imagePath = $this->storeImage($request);

        if ($imagePath !== null) {
            $attributes['image'] = $imagePath;
        }

        try {
            DB::transaction(fn () => $businessProfile->products()->create($attributes));
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                $this->deleteStoredImage($imagePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('business-products.index')
            ->with('status', 'Product or service created.');
    }

    public function edit(Request $request, int $product): View|RedirectResponse
    {
        $businessProfile = $this->ownerBusinessProfile($request);

        if ($businessProfile === null) {
            return redirect()->route('business-profile.create');
        }

        return view('business-products.edit', [
            'product' => $this->ownedProduct($businessProfile, $product),
        ]);
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        $businessProfile = $this->ownerBusinessProfile($request);

        if ($businessProfile === null) {
            return redirect()->route('business-profile.create');
        }

        $businessProduct = $this->ownedProduct($businessProfile, $product);
        $attributes = $this->validatedAttributes($request, $businessProduct->is_available);
        $previousImagePath = $businessProduct->image;
        $newImagePath = $this->storeImage($request);

        if ($newImagePath !== null) {
            $attributes['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($businessProduct, $attributes): void {
                $businessProduct->fill($attributes)->save();
            });
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                $this->deleteStoredImage($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== null && $previousImagePath !== null) {
            $this->deleteStoredImage($previousImagePath);
        }

        return redirect()
            ->route('business-products.index')
            ->with('status', 'Product or service updated.');
    }

    public function destroy(Request $request, int $product): RedirectResponse
    {
        $businessProfile = $this->ownerBusinessProfile($request);

        if ($businessProfile === null) {
            return redirect()->route('business-profile.create');
        }

        $businessProduct = $this->ownedProduct($businessProfile, $product);
        $imagePath = $businessProduct->image;

        DB::transaction(fn () => $businessProduct->delete());

        if ($imagePath !== null) {
            $this->deleteStoredImage($imagePath);
        }

        return redirect()
            ->route('business-products.index')
            ->with('status', 'Product or service deleted.');
    }

    private function ownerBusinessProfile(Request $request): ?BusinessProfile
    {
        $user = $request->user();

        abort_unless($user instanceof User && $user->isBusinessOwner(), 403);

        return $user->businessProfile()->first();
    }

    private function ownedProduct(BusinessProfile $businessProfile, int $productId): Product
    {
        return $businessProfile->products()->findOrFail($productId);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAttributes(Request $request, ?bool $currentAvailability = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'item_type' => ['required', Rule::in(['product', 'service'])],
            'is_available' => ['sometimes', 'boolean'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);

        $validated['is_available'] = $request->boolean(
            'is_available',
            $currentAvailability ?? true
        );

        return Arr::except($validated, ['image']);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $image = $request->file('image');

        if (! $image instanceof UploadedFile) {
            throw new RuntimeException('The product image upload is invalid.');
        }

        $path = $image->storePublicly('business-items', config('filesystems.media_disk'));

        if ($path === false) {
            throw new RuntimeException('The product image could not be stored.');
        }

        return $path;
    }

    private function deleteStoredImage(string $path): void
    {
        $segments = explode('/', $path);

        if (
            ! str_starts_with($path, 'business-items/')
            || str_contains($path, '\\')
            || in_array('', $segments, true)
            || in_array('.', $segments, true)
            || in_array('..', $segments, true)
        ) {
            throw new RuntimeException('The stored product image path is invalid.');
        }

        $disk = Storage::disk(config('filesystems.media_disk'));

        if (! $disk->exists($path)) {
            return;
        }

        if (! $disk->delete($path)) {
            throw new RuntimeException('The stored product image could not be deleted.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Product;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class BusinessProductController extends Controller
{
    /** Extra photos allowed per product or service, besides the main image. */
    private const MAX_GALLERY_PHOTOS = 6;

    public function __construct(private readonly ImageOptimizer $images) {}

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
        $galleryPaths = [];

        if ($imagePath !== null) {
            $attributes['image'] = $imagePath;
        }

        try {
            $galleryPaths = $this->storeGallery($request);

            DB::transaction(function () use ($businessProfile, $attributes, $galleryPaths): void {
                $product = $businessProfile->products()->create($attributes);
                $this->attachGallery($product, $galleryPaths, 0);
            });
        } catch (Throwable $exception) {
            $this->deleteStoredImages([$imagePath, ...$galleryPaths]);

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

        // Only photos that belong to this product can be removed.
        $removedPhotos = $businessProduct->images()
            ->whereIn('id', array_map('intval', (array) $request->input('remove_gallery', [])))
            ->get();
        $newPhotoCount = count($request->file('gallery') ?? []);

        if ($businessProduct->images()->count() - $removedPhotos->count() + $newPhotoCount > self::MAX_GALLERY_PHOTOS) {
            throw ValidationException::withMessages([
                'gallery' => 'A product or service can have at most '.self::MAX_GALLERY_PHOTOS.' extra photos. Remove some first.',
            ]);
        }

        $previousImagePath = $businessProduct->image;
        $newImagePath = $this->storeImage($request);
        $galleryPaths = [];

        if ($newImagePath !== null) {
            $attributes['image'] = $newImagePath;
        }

        try {
            $galleryPaths = $this->storeGallery($request);

            DB::transaction(function () use ($businessProduct, $attributes, $removedPhotos, $galleryPaths): void {
                $businessProduct->fill($attributes)->save();

                if ($removedPhotos->isNotEmpty()) {
                    $businessProduct->images()->whereIn('id', $removedPhotos->modelKeys())->delete();
                }

                $this->attachGallery(
                    $businessProduct,
                    $galleryPaths,
                    (int) $businessProduct->images()->max('sort_order') + 1
                );
            });
        } catch (Throwable $exception) {
            $this->deleteStoredImages([$newImagePath, ...$galleryPaths]);

            throw $exception;
        }

        if ($newImagePath !== null && $previousImagePath !== null) {
            $this->deleteStoredImage($previousImagePath);
        }

        $this->deleteStoredImages($removedPhotos->pluck('path')->all());

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
        $galleryPaths = $businessProduct->images()->pluck('path')->all();

        DB::transaction(fn () => $businessProduct->delete());

        $this->deleteStoredImages([$imagePath, ...$galleryPaths]);

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
        $photoRules = [
            'image',
            'mimes:jpg,jpeg,png,webp',
            'extensions:jpg,jpeg,png,webp',
            'max:4096',
        ];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'item_type' => ['required', Rule::in(['product', 'service'])],
            'is_available' => ['sometimes', 'boolean'],
            'image' => ['nullable', ...$photoRules],
            'gallery' => ['nullable', 'array', 'max:'.self::MAX_GALLERY_PHOTOS],
            'gallery.*' => $photoRules,
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['integer'],
        ]);

        $validated['is_available'] = $request->boolean(
            'is_available',
            $currentAvailability ?? true
        );

        return Arr::except($validated, ['image', 'gallery', 'remove_gallery']);
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

        $path = $this->images->store($image, 'business-items', 1280);

        if ($path === false) {
            throw new RuntimeException('The product image could not be stored.');
        }

        return $path;
    }

    /**
     * @return list<string>
     */
    private function storeGallery(Request $request): array
    {
        $paths = [];

        foreach ($request->file('gallery') ?? [] as $photo) {
            if (! $photo instanceof UploadedFile) {
                throw new RuntimeException('A gallery photo upload is invalid.');
            }

            $path = $this->images->store($photo, 'business-items/gallery', 1280);

            if ($path === false) {
                throw new RuntimeException('A gallery photo could not be stored.');
            }

            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * @param  list<string>  $paths
     */
    private function attachGallery(Product $product, array $paths, int $startOrder): void
    {
        foreach (array_values($paths) as $offset => $path) {
            $product->images()->create([
                'path' => $path,
                'sort_order' => $startOrder + $offset,
            ]);
        }
    }

    /**
     * @param  array<int, string|null>  $paths
     */
    private function deleteStoredImages(array $paths): void
    {
        foreach (array_filter($paths) as $path) {
            $this->deleteStoredImage($path);
        }
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

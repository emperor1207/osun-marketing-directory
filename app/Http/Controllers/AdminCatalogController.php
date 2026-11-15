<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminCatalogController extends Controller
{
    public function categories(): View
    {
        return view('admin.catalog.categories', [
            'categories' => Category::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Category::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug(Category::class, $validated['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories')->with('status', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $category->fill([
            ...$validated,
            'slug' => $this->uniqueSlug(Category::class, $validated['name'], $category->id),
            'is_active' => $request->boolean('is_active'),
        ])->save();

        return redirect()->route('admin.categories')->with('status', 'Category updated.');
    }

    public function locations(): View
    {
        return view('admin.catalog.locations', [
            'localGovernmentAreas' => LocalGovernmentArea::query()
                ->orderBy('name')
                ->paginate(20),
            'allLocalGovernmentAreas' => LocalGovernmentArea::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'towns' => Town::query()
                ->with('localGovernmentArea:id,name')
                ->orderBy('name')
                ->paginate(30, ['*'], 'town_page'),
        ]);
    }

    public function storeLocalGovernmentArea(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:local_government_areas,name'],
            'headquarters' => ['nullable', 'string', 'max:255'],
        ]);

        LocalGovernmentArea::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug(LocalGovernmentArea::class, $validated['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.locations')->with('status', 'Local government area created.');
    }

    public function updateLocalGovernmentArea(Request $request, LocalGovernmentArea $localGovernmentArea): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('local_government_areas', 'name')->ignore($localGovernmentArea->id),
            ],
            'headquarters' => ['nullable', 'string', 'max:255'],
        ]);

        $localGovernmentArea->fill([
            ...$validated,
            'slug' => $this->uniqueSlug(LocalGovernmentArea::class, $validated['name'], $localGovernmentArea->id),
            'is_active' => $request->boolean('is_active'),
        ])->save();

        return redirect()->route('admin.locations')->with('status', 'Local government area updated.');
    }

    public function storeTown(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'local_government_area_id' => ['required', 'integer', 'exists:local_government_areas,id'],
        ]);
        $this->validateTownName($validated['name'], (int) $validated['local_government_area_id']);

        Town::query()->create([
            ...$validated,
            'slug' => $this->uniqueTownSlug($validated['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.locations')->with('status', 'Town created.');
    }

    public function updateTown(Request $request, Town $town): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'local_government_area_id' => ['required', 'integer', 'exists:local_government_areas,id'],
        ]);
        $this->validateTownName(
            $validated['name'],
            (int) $validated['local_government_area_id'],
            $town->id
        );

        $town->fill([
            ...$validated,
            'slug' => $this->uniqueTownSlug($validated['name'], $town->id),
            'is_active' => $request->boolean('is_active'),
        ])->save();

        return redirect()->route('admin.locations')->with('status', 'Town updated.');
    }

    /**
     * @param  class-string<Category|LocalGovernmentArea>  $modelClass
     */
    private function uniqueSlug(string $modelClass, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'location';
        $slug = $baseSlug;
        $suffix = 1;

        while (
            $modelClass::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.++$suffix;
        }

        return $slug;
    }

    private function uniqueTownSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'town';
        $slug = $baseSlug;
        $suffix = 1;

        while (
            Town::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.++$suffix;
        }

        return $slug;
    }

    private function validateTownName(string $name, int $localGovernmentAreaId, ?int $ignoreId = null): void
    {
        $validator = validator(
            ['name' => $name],
            [
                'name' => [
                    Rule::unique('towns', 'name')
                        ->where('local_government_area_id', $localGovernmentAreaId)
                        ->ignore($ignoreId),
                ],
            ],
            ['name.unique' => 'This town already exists in the selected local government area.']
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }
}

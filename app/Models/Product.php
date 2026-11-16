<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'item_type',
        'is_available',
    ];

    /**
     * @return BelongsTo<BusinessProfile, $this>
     */
    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    /**
     * Extra photos. Sort them with sort_order when displaying (kept out of the
     * relation so count() and max() also work on PostgreSQL).
     *
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Public URLs of the main photo followed by the extra photos.
     *
     * @return list<string>
     */
    public function galleryUrls(): array
    {
        $disk = Storage::disk(config('filesystems.media_disk'));
        $urls = [];

        $extraPaths = $this->images->sortBy([['sort_order', 'asc'], ['id', 'asc']])->pluck('path')->all();

        foreach (array_filter([$this->image, ...$extraPaths]) as $path) {
            if ($disk->exists($path)) {
                $urls[] = $disk->url($path);
            }
        }

        return $urls;
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }
}

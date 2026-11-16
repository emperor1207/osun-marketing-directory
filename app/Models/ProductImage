<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read string|null $url
 */
class ProductImage extends Model
{
    protected $fillable = [
        'path',
        'sort_order',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function url(): Attribute
    {
        return Attribute::get(function (): ?string {
            $disk = Storage::disk(config('filesystems.media_disk'));

            return $disk->exists($this->path) ? $disk->url($this->path) : null;
        });
    }
}

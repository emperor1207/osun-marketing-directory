<?php

namespace App\Models;

use Database\Factories\BusinessProfileFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read string|null $logo_url
 * @property-read string|null $cover_image_url
 */
class BusinessProfile extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @use HasFactory<BusinessProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'town_id',
        'business_name',
        'slug',
        'description',
        'street_address',
        'phone',
        'whatsapp',
        'email',
        'website',
        'logo',
        'cover_image',
        'latitude',
        'longitude',
        'status',
        'rejection_reason',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->mediaUrl($this->logo))->shouldCache();
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->mediaUrl($this->cover_image))->shouldCache();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Town, $this>
     */
    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<BusinessReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(BusinessReport::class);
    }

    /**
     * @return HasMany<BusinessProfileView, $this>
     */
    public function views(): HasMany
    {
        return $this->hasMany(BusinessProfileView::class);
    }
    /**
     * @return HasMany<BusinessHour, $this>
     */
    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function isOpenNow(): bool
    {
        $now = now('Africa/Lagos');
        $dayOfWeek = $now->dayOfWeek; // 0 (Sunday) - 6 (Saturday)
        $currentTime = $now->format('H:i:s');

        $businessHour = $this->businessHours()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_closed', false)
            ->first();

        if (!$businessHour) {
            return false;
        }

        return $currentTime >= $businessHour->open_time && $currentTime <= $businessHour->close_time;
    }


    private function mediaUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $disk = Storage::disk(config('filesystems.media_disk'));

        return $disk->exists($path) ? $disk->url($path) : null;
    }
}

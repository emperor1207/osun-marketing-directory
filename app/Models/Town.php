<?php

namespace App\Models;

use Database\Factories\TownFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Town extends Model
{
    /** @use HasFactory<TownFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'local_government_area_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<LocalGovernmentArea, $this>
     */
    public function localGovernmentArea(): BelongsTo
    {
        return $this->belongsTo(LocalGovernmentArea::class);
    }

    /**
     * @return HasMany<BusinessProfile, $this>
     */
    public function businessProfiles(): HasMany
    {
        return $this->hasMany(BusinessProfile::class);
    }
}

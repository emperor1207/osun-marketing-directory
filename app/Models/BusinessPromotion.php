<?php

namespace App\Models;

use Database\Factories\BusinessPromotionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPromotion extends Model
{
    /** @use HasFactory<BusinessPromotionFactory> */
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'title',
        'description',
        'discount_code',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }
}

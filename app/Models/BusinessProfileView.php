<?php

namespace App\Models;

use Database\Factories\BusinessProfileViewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProfileView extends Model
{
    /** @use HasFactory<BusinessProfileViewFactory> */
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'visitor_key',
        'viewed_at',
    ];

    /**
     * @return BelongsTo<BusinessProfile, $this>
     */
    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }
}

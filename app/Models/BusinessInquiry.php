<?php

namespace App\Models;

use Database\Factories\BusinessInquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessInquiry extends Model
{
    /** @use HasFactory<BusinessInquiryFactory> */
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'user_id',
        'sender_name',
        'sender_email',
        'sender_phone',
        'message',
        'status',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

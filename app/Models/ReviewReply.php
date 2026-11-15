<?php

namespace App\Models;

use Database\Factories\ReviewReplyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewReply extends Model
{
    /** @use HasFactory<ReviewReplyFactory> */
    use HasFactory;

    protected $fillable = [
        'review_id',
        'business_owner_id',
        'content',
        'status',
        'moderated_by',
        'moderated_at',
    ];

    /**
     * @return BelongsTo<Review, $this>
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function businessOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_owner_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    protected function casts(): array
    {
        return [
            'moderated_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use Database\Factories\LocalGovernmentAreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocalGovernmentArea extends Model
{
    /** @use HasFactory<LocalGovernmentAreaFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'headquarters',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Town, $this>
     */
    public function towns(): HasMany
    {
        return $this->hasMany(Town::class);
    }
}

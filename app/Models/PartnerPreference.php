<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerPreference extends Model
{
    protected $fillable = [
        'user_id',
        'preferred_gender',
        'age_min',
        'age_max',
        'height_min_cm',
        'height_max_cm',
        'preferred_country',
        'preferred_division',
        'preferred_district',
        'religions',
        'marital_statuses',
        'education_level',
        'profession',
        'diet',
        'smoking',
        'drinking',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'religions' => 'array',
            'marital_statuses' => 'array',
            'age_min' => 'integer',
            'age_max' => 'integer',
            'height_min_cm' => 'integer',
            'height_max_cm' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ageRangeLabel(): string
    {
        if (! $this->age_min && ! $this->age_max) {
            return 'Any age';
        }

        return ($this->age_min ?: '18').' - '.($this->age_max ?: '60').' yrs';
    }

    public function scopeMatchingAge(Builder $query, int $age): Builder
    {
        return $query->where('age_min', '<=', $age)->where('age_max', '>=', $age);
    }
}

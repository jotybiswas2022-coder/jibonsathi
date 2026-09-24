<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SuccessStory extends Model
{
    protected $fillable = [
        'title',
        'groom_name',
        'bride_name',
        'location',
        'story',
        'photo_path',
        'married_on',
        'is_published',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'married_on' => 'date',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Media::url($this->photo_path) : null;
    }

    public function coupleLabel(): string
    {
        return "{$this->groom_name} & {$this->bride_name}";
    }

    public function initials(): string
    {
        return strtoupper(mb_substr($this->groom_name, 0, 1).mb_substr($this->bride_name, 0, 1));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}

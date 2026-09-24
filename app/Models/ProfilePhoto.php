<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilePhoto extends Model
{
    protected $fillable = [
        'user_id',
        'path',
        'caption',
        'is_primary',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        return Media::url($this->path, $this->user?->name ?? 'Jibon Sathi');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = [
        'user_id',
        'level',
        'degree',
        'institution',
        'field_of_study',
        'start_year',
        'end_year',
        'is_highest',
    ];

    protected function casts(): array
    {
        return [
            'is_highest' => 'boolean',
            'start_year' => 'integer',
            'end_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

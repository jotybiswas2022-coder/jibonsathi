<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifestyleDetail extends Model
{
    protected $fillable = [
        'user_id',
        'diet',
        'smoking',
        'drinking',
        'hobbies',
        'interests',
        'about_lifestyle',
    ];

    protected function casts(): array
    {
        return [
            'hobbies' => 'array',
            'interests' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return list<string>
     */
    public function allInterests(): array
    {
        return array_values(array_unique(array_merge($this->interests ?? [], $this->hobbies ?? [])));
    }
}

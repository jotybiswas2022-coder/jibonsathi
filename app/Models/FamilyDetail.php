<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyDetail extends Model
{
    protected $fillable = [
        'user_id',
        'family_type',
        'family_status',
        'father_occupation',
        'mother_occupation',
        'brothers',
        'sisters',
        'family_income_range',
        'about_family',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function siblingLabel(): string
    {
        $brothers = (int) $this->brothers;
        $sisters = (int) $this->sisters;

        if ($brothers === 0 && $sisters === 0) {
            return 'No siblings';
        }

        return "{$brothers} brother(s), {$sisters} sister(s)";
    }
}

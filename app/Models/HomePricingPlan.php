<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Model;

class HomePricingPlan extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_highlighted' => 'boolean',
            'is_visible' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }
}

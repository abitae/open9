<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSectionSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }
}

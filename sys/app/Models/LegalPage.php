<?php

namespace App\Models;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
        ];
    }
}

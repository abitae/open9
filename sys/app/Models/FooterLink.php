<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterLink extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_external' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(FooterLinkGroup::class, 'footer_link_group_id');
    }
}

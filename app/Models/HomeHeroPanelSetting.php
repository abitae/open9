<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeHeroPanelSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'show_site_name_chip' => 'boolean',
        ];
    }
}

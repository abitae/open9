<?php

namespace App\Models;

use App\Enums\StorageDriver;
use Illuminate\Database\Eloquent\Model;

class StorageSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'driver' => StorageDriver::class,
            'driver_changed_at' => 'datetime',
        ];
    }
}

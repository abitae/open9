<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueCodeService
{
    /**
     * @param  class-string<Model>  $model
     */
    public function make(string $model, string $column, string $prefix): string
    {
        do {
            $code = $prefix.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while ($model::query()->where($column, $code)->exists());

        return $code;
    }
}

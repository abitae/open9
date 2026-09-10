<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Enums\TestimonialType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => TestimonialType::class,
            'status' => RecordStatus::class,
        ];
    }
}

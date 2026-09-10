<?php

namespace App\Models;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseLesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'resources' => 'array',
            'is_preview' => 'boolean',
            'status' => RecordStatus::class,
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function setResourcesAttribute(mixed $value): void
    {
        if ($value === null) {
            $this->attributes['resources'] = null;

            return;
        }

        if (is_array($value)) {
            $this->attributes['resources'] = json_encode($value);

            return;
        }

        if (is_string($value) && trim($value) === '') {
            $this->attributes['resources'] = null;

            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            $this->attributes['resources'] = json_encode(is_array($decoded)
                ? $decoded
                : collect(preg_split('/\r\n|\r|\n/', $value))->map(fn (string $line): string => trim($line))->filter()->values()->all());
        }
    }
}

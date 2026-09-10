<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function log(string $action, string $module, ?Model $model = null, array $oldValues = [], array $newValues = []): AuditLog
    {
        /** @var Request $request */
        $request = request();

        return AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'module' => $module,
            'model_type' => $model?->getMorphClass(),
            'model_id' => $model?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

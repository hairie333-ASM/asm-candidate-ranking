<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log a security or business event.
     */
    public function log(
        string $action,
        ?string $recordType = null,
        ?int $recordId = null,
        ?string $description = null,
        ?User $user = null
    ): AuditLog {
        $actor = $user ?? auth()->user();

        return AuditLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'description' => $description,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'System/CLI',
            'created_at' => now(),
        ]);
    }
}

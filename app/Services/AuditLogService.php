<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    /**
     * Record an audit log entry for security and legal compliance.
     *
     * @param User|null $user
     * @param string $action
     * @param string $type
     * @param int $id
     * @param array $old
     * @param array $new
     * @return AuditLog
     */
    public function log(
        ?User $user,
        string $action,
        string $type,
        int $id,
        array $old = [],
        array $new = []
    ): AuditLog {
        $ip = request()->ip() ?? '127.0.0.1';
        $userAgent = request()->userAgent() ?? 'CLI/System';

        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'target_type' => $type,
            'target_id' => $id,
            'old_values' => empty($old) ? null : $old,
            'new_values' => empty($new) ? null : $new,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }
}

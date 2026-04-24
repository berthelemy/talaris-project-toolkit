<?php

namespace App\Libraries\Auth;

use App\Models\AuthAuditLogModel;

class AuditLogger
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function log(string $eventType, string $status, ?int $userId = null, array $metadata = []): void
    {
        $model = new AuthAuditLogModel();
        $request = service('request');

        $ipAddress = method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null;
        $userAgent = method_exists($request, 'getUserAgent') ? (string) $request->getUserAgent() : null;

        $model->insert([
            'user_id'    => $userId,
            'event_type' => $eventType,
            'status'     => $status,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent === null ? null : substr($userAgent, 0, 255),
            'metadata'   => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
        ]);
    }
}

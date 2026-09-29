<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class ActivityLogger
{
    public function log(string $action, string $description, ?Model $subject = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'description' => mb_strimwidth($description, 0, 250, '…'),
                'ip_address' => request()?->ip(),
            ]);
        } catch (Throwable $e) {
            // Logging must never break the request.
            report($e);
        }
    }
}

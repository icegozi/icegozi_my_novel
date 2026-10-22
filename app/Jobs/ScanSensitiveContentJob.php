<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Chapter;
use App\Services\ContentFilterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScanSensitiveContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout for the job in seconds to prevent hanging.
     */
    public int $timeout = 60;

    /**
     * Max tries for queue execution.
     */
    public int $tries = 3;

    public function __construct(public Chapter $chapter)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ContentFilterService $filterService): void
    {
        $payload = $this->chapter->title . ' ' . $this->chapter->content;
        $result = $filterService->scan($payload);

        if ($result['has_violation']) {
            $oldStatus = $this->chapter->status;

            if ($result['max_severity'] === 'auto_block') {
                $this->chapter->status = 'rejected';
                $action = 'content_filter_auto_blocked';
            } else {
                $this->chapter->status = 'pending_approval';
                $action = 'content_filter_flagged_for_review';
            }

            $this->chapter->save();

            AuditLog::create([
                'user_id' => $this->chapter->novel->author_id ?? null,
                'action' => $action,
                'target_type' => 'Chapter',
                'target_id' => $this->chapter->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => $this->chapter->status,
                    'violations' => $result['violations'],
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System Sensitive Content Scanner',
            ]);
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Chapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublishScheduledChaptersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-scheduled-chapters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically publish chapters that have reached their scheduled release time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        $dueChapters = Chapter::with('novel')
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->get();

        if ($dueChapters->isEmpty()) {
            $this->info('No scheduled chapters due for publishing.');
            return self::SUCCESS;
        }

        $publishedCount = 0;

        foreach ($dueChapters as $chapter) {
            DB::transaction(function () use ($chapter, $now, &$publishedCount) {
                $oldScheduledAt = $chapter->scheduled_at;

                $chapter->update([
                    'status' => 'published',
                    'published_at' => $now,
                ]);

                // Update novel statistics
                $novel = $chapter->novel;
                if ($novel) {
                    $novel->total_chapters = $novel->chapters()->where('status', 'published')->count();
                    $novel->total_words = $novel->chapters()->where('status', 'published')->sum('word_count');
                    $novel->save();
                }

                // Create audit log for regulatory compliance and transparency
                AuditLog::create([
                    'user_id' => $novel?->author_id,
                    'action' => 'chapter_auto_published_by_schedule',
                    'target_type' => 'Chapter',
                    'target_id' => $chapter->id,
                    'old_values' => [
                        'status' => 'scheduled',
                        'scheduled_at' => $oldScheduledAt?->toIso8601String(),
                    ],
                    'new_values' => [
                        'status' => 'published',
                        'published_at' => $now->toIso8601String(),
                    ],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Console Scheduler: app:publish-scheduled-chapters',
                ]);

                $publishedCount++;
            });
        }

        $this->info("Published {$publishedCount} scheduled chapters successfully.");

        return self::SUCCESS;
    }
}

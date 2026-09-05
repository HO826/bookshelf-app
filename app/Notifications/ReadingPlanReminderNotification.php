<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    private ReadingPlan $readingPlan;

    private string $timing;

    public function __construct(ReadingPlan $readingPlan, string $timing)
    {
        $this->readingPlan = $readingPlan;
        $this->timing = $timing;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return 'reading_plan_reminder';
    }

    public function toArray(object $notifiable): array|string
    {
        $rawTargetDate = $this->readingPlan->target_date;
        $targetDateStr = ($rawTargetDate instanceof \DateTimeInterface)
            ? $rawTargetDate->format('Y-m-d')
            : (string) $rawTargetDate;
        $bookTitle = optional($this->readingPlan->book)->title ?? '書籍';

        $data = [
            'reading_plan_id' => (int) $this->readingPlan->id,
            'book_title' => (string) $bookTitle,
            'target_date' => (string) $targetDateStr,
            'timing' => (string) $this->timing,
            'title' => (string) $this->title(),
            'body' => (string) $this->body(),
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function title(): string
    {
        return match ($this->timing) {
            'three_days_before' => '読書計画の期日が近づいています',
            'on_due_date' => '読書計画の期日当日です',
            'three_days_after' => '読書計画の期日を過ぎています',
            default => '読書計画の通知',
        };
    }

    private function body(): string
    {
        $bookTitle = optional($this->readingPlan->book)->title ?? '書籍';

        return match ($this->timing) {
            'three_days_before' => "「{$bookTitle}」の期日は3日後です。",
            'on_due_date' => "「{$bookTitle}」の期日は今日です。",
            'three_days_after' => "「{$bookTitle}」の期日を3日過ぎています。",
            default => '読書計画を確認してください。',
        };
    }
}

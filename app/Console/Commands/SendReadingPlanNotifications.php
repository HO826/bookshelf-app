<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReadingPlanNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plan:send-notifications';

    protected $description = '期限切れステータスの更新および読書計画のリマインダー通知を送信します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        ReadingPlan::where('status', '!=', ReadingPlanStatus::Completed)
            ->where('status', '!=', ReadingPlanStatus::Expired)
            ->where('target_date', '<', $today)
            ->update([
                'status' => ReadingPlanStatus::Expired,
            ]);

        $plans = ReadingPlan::with(['user', 'book'])
            ->where('status', '!=', ReadingPlanStatus::Completed)
            ->get();

        foreach ($plans as $plan) {
            if (! $plan->user || ! $plan->target_date) {
                continue;
            }

            $targetDate = Carbon::parse($plan->target_date)->startOfDay();

            if ($targetDate->isSameDay($today->copy()->addDays(3))) {
                $plan->user->notify(new ReadingPlanReminderNotification($plan, 'three_days_before'));
            }

            if ($targetDate->isSameDay($today)) {
                $plan->user->notify(new ReadingPlanReminderNotification($plan, 'on_due_date'));
            }

            if ($targetDate->isSameDay($today->copy()->subDays(3))) {
                $plan->user->notify(new ReadingPlanReminderNotification($plan, 'three_days_after'));
            }
        }

        $this->info('ステータス更新および通知送信バッチ処理が完了しました。');

        return Command::SUCCESS;
    }
}

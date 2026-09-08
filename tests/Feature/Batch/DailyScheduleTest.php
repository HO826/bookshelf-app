<?php

namespace Tests\Feature\Batch;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DailyScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_期日経過した計画のステータス更新とリマインダー通知が実行される(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $expiredPlan = ReadingPlan::factory()->for($user)->create([
            'target_date' => now()->subDays(3)->format('Y-m-d'),
            'status' => 'in_progress',
        ]);

        $todayPlan = ReadingPlan::factory()->for($user)->create([
            'target_date' => now()->format('Y-m-d'),
            'status' => 'in_progress',
        ]);

        $threeDaysBeforePlan = ReadingPlan::factory()->for($user)->create([
            'target_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'in_progress',
        ]);

        $completedPlan = ReadingPlan::factory()->for($user)->create([
            'target_date' => now()->subDay()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $this->artisan('reading-plan:send-notifications')
            ->assertExitCode(0);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $expiredPlan->id,
            'status' => 'expired',
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $todayPlan->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $threeDaysBeforePlan->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $completedPlan->id,
            'status' => 'completed',
        ]);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification) use ($expiredPlan) {
                $mail = $notification->toMail($expiredPlan->user);
                $array = $notification->toArray($expiredPlan->user);

                return $array['reading_plan_id'] === $expiredPlan->id
                    && $array['timing'] === 'three_days_after';
            }
        );

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification) use ($todayPlan) {
                $mail = $notification->toMail($todayPlan->user);
                $array = $notification->toArray($todayPlan->user);

                return $array['reading_plan_id'] === $todayPlan->id
                    && $array['timing'] === 'on_due_date';
            }
        );

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification) use ($threeDaysBeforePlan) {
                $mail = $notification->toMail($threeDaysBeforePlan->user);
                $array = $notification->toArray($threeDaysBeforePlan->user);

                return $array['reading_plan_id'] === $threeDaysBeforePlan->id
                    && $array['timing'] === 'three_days_before';
            }
        );

        Notification::assertNotSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification) use ($completedPlan) {
                $data = $notification->toArray($completedPlan->user);

                return $data['reading_plan_id'] === $completedPlan->id;
            }
        );
    }
}

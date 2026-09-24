<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Report;
use App\Models\User;
use App\Notifications\AccountStatusNotification;
use App\Notifications\NewReportNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportService
{
    /**
     * File a report against another member.
     */
    public function create(User $reporter, User $reported, string $reason, ?string $description = null): Report
    {
        if ($reporter->id === $reported->id) {
            throw ValidationException::withMessages(['reason' => 'You cannot report your own profile.']);
        }

        $duplicate = Report::query()
            ->where('reporter_id', $reporter->id)
            ->where('reported_user_id', $reported->id)
            ->whereIn('status', [Report::STATUS_PENDING, Report::STATUS_INVESTIGATING])
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['reason' => 'You already have an open report for this member.']);
        }

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $reported->id,
            'reason' => $reason,
            'description' => $description,
            'status' => Report::STATUS_PENDING,
        ]);

        foreach (User::admins()->get() as $admin) {
            $admin->notify(new NewReportNotification($report));
        }

        return $report;
    }

    public function markInvestigating(Report $report, User $admin, ?string $note = null): Report
    {
        $report->update([
            'status' => Report::STATUS_INVESTIGATING,
            'admin_note' => $note ?? $report->admin_note,
            'handled_by' => $admin->id,
        ]);

        return $report->refresh();
    }

    public function resolve(Report $report, User $admin, ?string $note = null): Report
    {
        $report->update([
            'status' => Report::STATUS_RESOLVED,
            'admin_note' => $note,
            'handled_by' => $admin->id,
            'resolved_at' => now(),
        ]);

        return $report->refresh();
    }

    public function dismiss(Report $report, User $admin, ?string $note = null): Report
    {
        $report->update([
            'status' => Report::STATUS_DISMISSED,
            'admin_note' => $note,
            'handled_by' => $admin->id,
            'resolved_at' => now(),
        ]);

        return $report->refresh();
    }

    /**
     * Suspend a reported member and close the report in one action.
     */
    public function suspendReportedUser(Report $report, User $admin, ?string $note = null): Report
    {
        DB::transaction(function () use ($report, $admin, $note) {
            $reported = $report->reportedUser;

            if ($reported) {
                $reported->forceFill(['status' => User::STATUS_SUSPENDED])->save();
                $reported->profile?->forceFill(['profile_status' => 'suspended'])->save();
                $reported->notify(new AccountStatusNotification('suspended', $note));
            }

            $report->update([
                'status' => Report::STATUS_RESOLVED,
                'admin_note' => $note ?? 'Account suspended by moderation team.',
                'handled_by' => $admin->id,
                'resolved_at' => now(),
            ]);
        });

        return $report->refresh();
    }

    /* -----------------------------------------------------------------
     |  Blocks
     | ----------------------------------------------------------------- */

    public function block(User $blocker, User $blocked, ?string $reason = null): bool
    {
        if ($blocker->id === $blocked->id) {
            return false;
        }

        Block::firstOrCreate(
            ['blocker_id' => $blocker->id, 'blocked_id' => $blocked->id],
            ['reason' => $reason]
        );

        return true;
    }

    public function unblock(User $blocker, User $blocked): void
    {
        Block::query()
            ->where('blocker_id', $blocker->id)
            ->where('blocked_id', $blocked->id)
            ->delete();
    }
}

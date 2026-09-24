<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Report;
use App\Models\Verification;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Identity verification requests, member reports and a couple of blocks
 * so the moderation queues and admin reporting screens have content.
 */
class ModerationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('is_admin', true)->first();

        $members = User::query()->where('is_admin', false)->get()->values();
        $grooms = User::query()->where('is_admin', false)->whereHas('profile', fn ($q) => $q->where('gender', 'male'))->get();
        $brides = User::query()->where('is_admin', false)->whereHas('profile', fn ($q) => $q->where('gender', 'female'))->get();

        $this->seedVerifications($admin, $members);
        $this->seedReports($admin, $grooms, $brides);
        $this->seedBlocks($grooms, $brides);
    }

    private function seedVerifications(?User $admin, $members): void
    {
        if (Verification::query()->exists()) {
            return;
        }

        foreach ($members->take(10) as $i => $member) {
            $approved = $i % 5 !== 3;

            Verification::create([
                'user_id' => $member->id,
                'type' => $i % 3 === 0 ? 'profile' : ($i % 2 === 0 ? 'phone' : 'email'),
                'status' => $approved ? Verification::STATUS_APPROVED : Verification::STATUS_PENDING,
                'document_path' => null,
                'document_type' => $i % 3 === 0 ? 'national_id' : null,
                'note' => $approved ? null : 'Document photo too blurry — please re-upload a clearer copy.',
                'admin_note' => $approved ? 'Looks genuine. Approved.' : null,
                'reviewed_by' => $approved ? $admin?->id : null,
                'reviewed_at' => $approved ? now()->subDays(rand(3, 40)) : null,
                'created_at' => now()->subDays(rand(2, 50)),
                'updated_at' => now()->subDays(rand(0, 40)),
            ]);

            // Keep profile.verification_status in sync.
            $member->profile?->forceFill([
                'verification_status' => $approved ? 'verified' : 'pending',
            ])->save();
        }

        // Two fresh profile verifications waiting in the queue.
        foreach ($members->skip(10)->take(2) as $member) {
            Verification::create([
                'user_id' => $member->id,
                'type' => 'profile',
                'status' => Verification::STATUS_PENDING,
                'document_type' => 'national_id',
                'note' => 'Please verify my profile.',
                'created_at' => now()->subHours(rand(5, 60)),
                'updated_at' => now()->subHours(rand(1, 6)),
            ]);

            $member->profile?->forceFill(['verification_status' => 'pending'])->save();
        }
    }

    private function seedReports(?User $admin, $grooms, $brides): void
    {
        if (Report::query()->exists()) {
            return;
        }

        $reasons = array_keys(Report::REASONS);

        $cases = [
            ['reason' => $reasons[0], 'description' => 'This profile looks like a fake account using someone else\u2019s photos.', 'status' => Report::STATUS_PENDING],
            ['reason' => $reasons[3], 'description' => 'Received repeated inappropriate messages after rejecting their interest.', 'status' => Report::STATUS_PENDING],
            ['reason' => $reasons[4], 'description' => 'Profile was reported for suspicious behaviour in a previous thread.', 'status' => Report::STATUS_RESOLVED],
        ];

        foreach ($cases as $i => $case) {
            $reporter = $grooms[$i % $grooms->count()];
            $reported = $brides[($i + 2) % $brides->count()];

            Report::create([
                'reporter_id' => $reporter->id,
                'reported_user_id' => $reported->id,
                'reason' => $case['reason'],
                'description' => $case['description'],
                'status' => $case['status'],
                'admin_note' => $case['status'] === Report::STATUS_RESOLVED ? 'No violation found; conversation monitored.' : null,
                'handled_by' => $case['status'] === Report::STATUS_RESOLVED ? $admin?->id : null,
                'resolved_at' => $case['status'] === Report::STATUS_RESOLVED ? now()->subDays(2) : null,
                'created_at' => now()->subDays(rand(1, 12)),
                'updated_at' => now()->subDays(rand(0, 8)),
            ]);
        }
    }

    private function seedBlocks($grooms, $brides): void
    {
        if (Block::query()->exists()) {
            return;
        }

        $g = $grooms->first();
        $b = $brides->nth(3)->last() ?? $brides->last();

        if ($g && $b && $g->id !== $b->id) {
            Block::create([
                'blocker_id' => $g->id,
                'blocked_id' => $b->id,
                'reason' => 'Prefers not to be contacted',
            ]);
        }
    }
}
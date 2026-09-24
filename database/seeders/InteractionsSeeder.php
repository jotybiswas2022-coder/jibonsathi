<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Interest;
use App\Models\MatchRecord;
use App\Models\Message;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Notifications\DatabaseNotification as Notification;
use Illuminate\Support\Str;

/**
 * Seeds interests (mixed statuses), favorites, match records,
 * conversations with message threads, profile views, and in-app
 * notifications — giving every section of the member area real content.
 */
class InteractionsSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::query()
            ->where('is_admin', false)
            ->whereNotNull('status')
            ->where('status', User::STATUS_ACTIVE)
            ->with('profile')
            ->get()
            ->values();

        if ($members->count() < 4) {
            $this->command?->warn('Not enough members seeded — run UsersAndProfilesSeeder first.');

            return;
        }

        $grooms = $members->where('profile.gender', 'male')->values();
        $brides = $members->where('profile.gender', 'female')->values();

        $this->seedInterests($grooms, $brides);
        $this->seedFavorites($grooms, $brides);
        $this->seedMatches($members);
        $this->seedProfileViews($members);
        $this->seedConversations($grooms, $brides);
        $this->seedNotifications($members);
    }

    private function seedInterests($grooms, $brides): void
    {
        if (Interest::query()->exists()) {
            return;
        }

        $statuses = [
            Interest::STATUS_PENDING,
            Interest::STATUS_ACCEPTED,
            Interest::STATUS_ACCEPTED,
            Interest::STATUS_REJECTED,
            Interest::STATUS_PENDING,
        ];

        $pairs = [];
        foreach ($grooms as $i => $groom) {
            // Each groom reaches out to 1–2 brides.
            $targets = max(1, $i % 2 + 1);
            for ($t = 0; $t < $targets; $t++) {
                $bride = $brides[($i * 2 + $t) % $brides->count()];
                $key = $groom->id.'-'.$bride->id;
                if (isset($pairs[$key])) {
                    continue;
                }
                $pairs[$key] = [$groom, $bride];
            }
        }

        // Some brides also reach out to grooms.
        foreach ($brides as $i => $bride) {
            if ($i % 3 !== 0) {
                continue;
            }
            $groom = $grooms[($i * 3) % $grooms->count()];
            $key = $groom->id.'-'.$bride->id;
            if (! isset($pairs[$key])) {
                $pairs[$key] = [$groom, $bride, true]; // bride is sender
            }
        }

        $index = 0;
        foreach ($pairs as $pair) {
            [$groom, $bride, $brideIsSender] = $pair + [2 => false];
            $status = $statuses[$index % count($statuses)];
            $senderId = $brideIsSender ? $bride->id : $groom->id;
            $receiverId = $brideIsSender ? $groom->id : $bride->id;

            Interest::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'status' => $status,
                'responded_at' => $status !== Interest::STATUS_PENDING ? now()->subDays(rand(1, 30)) : null,
                'created_at' => now()->subDays(rand(5, 45)),
                'updated_at' => now()->subDays(rand(1, 4)),
            ]);

            $index++;
        }
    }

    private function seedFavorites($grooms, $brides): void
    {
        if (Favorite::query()->exists()) {
            return;
        }

        foreach ($grooms->take(6) as $i => $groom) {
            Favorite::firstOrCreate([
                'user_id' => $groom->id,
                'favorite_user_id' => $brides[$i % $brides->count()]->id,
            ]);
        }

        foreach ($brides->take(6) as $i => $bride) {
            Favorite::firstOrCreate([
                'user_id' => $bride->id,
                'favorite_user_id' => $grooms[$i % $grooms->count()]->id,
            ]);
        }
    }

    private function seedMatches($members): void
    {
        if (MatchRecord::query()->exists()) {
            return;
        }

        foreach ($members->take(24) as $i => $member) {
            $candidate = $members[($i * 5 + 3) % $members->count()];
            if ($candidate->id === $member->id) {
                continue;
            }

            $percentage = 52 + (($i * 7) % 41); // 52–92

            MatchRecord::create([
                'user_id' => $member->id,
                'matched_user_id' => $candidate->id,
                'match_percentage' => $percentage,
                'reasons' => [
                    ['label' => 'Similar values', 'weight' => 25, 'matched' => true],
                    ['label' => 'Education match', 'weight' => 20, 'matched' => $percentage > 70],
                    ['label' => 'Location match', 'weight' => 15, 'matched' => $percentage > 60],
                ],
                'type' => MatchRecord::TYPE_RECOMMENDED,
                'is_mutual' => false,
                'last_calculated_at' => now(),
            ]);
        }
    }

    private function seedProfileViews($members): void
    {
        if (ProfileView::query()->exists()) {
            return;
        }

        foreach ($members->take(18) as $i => $member) {
            $viewer = $members[($i * 7 + 2) % $members->count()];
            if ($viewer->id === $member->id) {
                continue;
            }

            ProfileView::create([
                'user_id' => $member->id,
                'viewer_id' => $viewer->id,
                'viewed_at' => now()->subHours(rand(1, 24 * 14)),
            ]);
        }
    }

    private function seedConversations($grooms, $brides): void
    {
        if (Conversation::query()->exists()) {
            return;
        }

        $threads = [
            // [groom index, bride index, [messages as [side, body]]]
            [0, 0, [
                ['groom', 'Assalamu Alaikum! I really liked your profile.'],
                ['bride', 'Wa Alaikum Assalam! Thank you, yours too.'],
                ['groom', 'Would you like to get to know each other?'],
                ['bride', 'Yes, I\u2019d be happy to.'],
            ]],
            [1, 1, [
                ['groom', 'Hello! Hope you\u2019re having a good day.'],
                ['bride', 'Hi! Yes, thanks. How about you?'],
                ['groom', 'Great. I saw you love travelling — where was your last trip?'],
                ['bride', 'Sajek Valley. The sunrise was unreal!'],
            ]],
            [2, 2, [
                ['groom', 'Your education background really impressed me.'],
                ['bride', 'Thank you! I noticed you\u2019re an engineer too.'],
                ['groom', 'Indeed. Small world!'],
            ]],
            [3, 3, [
                ['bride', 'Hi, I accepted your interest. Nice to connect!'],
                ['groom', 'That\u2019s wonderful. I hope we can have a good conversation.'],
            ]],
            [4, 4, [
                ['groom', 'Would it be okay to involve our families soon?'],
                ['bride', 'Of course, that\u2019s exactly what I\u2019m looking for.'],
                ['groom', 'Perfect. I\u2019ll ask my parents to reach out.'],
            ]],
        ];

        foreach ($threads as [$gIdx, $bIdx, $messages]) {
            $groom = $grooms[$gIdx % $grooms->count()];
            $bride = $brides[$bIdx % $brides->count()];

            [$one, $two] = $groom->id < $bride->id ? [$groom, $bride] : [$bride, $groom];

            $conversation = Conversation::create([
                'user_one_id' => $one->id,
                'user_two_id' => $two->id,
                'user_one_last_read_at' => now(),
                'user_two_last_read_at' => null,
            ]);

            $last = null;
            foreach ($messages as $k => [$side, $body]) {
                $sender = $side === 'groom' ? $groom : $bride;
                $created = now()->subDays(count($messages) - $k)->subHours(rand(1, 6));

                $last = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender->id,
                    'body' => $body,
                    'read_at' => $k < count($messages) - 1 ? $created->copy()->addMinutes(5) : null,
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }

            if ($last) {
                $conversation->update([
                    'last_message_id' => $last->id,
                    'last_message_at' => $last->created_at,
                ]);
            }
        }
    }

    private function seedNotifications($members): void
    {
        if (Notification::query()->exists()) {
            return;
        }

        foreach ($members->take(12) as $i => $member) {
            $actor = $members[($i * 5 + 4) % $members->count()];
            if ($actor->id === $member->id) {
                continue;
            }

            $notifications = [
                [
                    'title' => 'New Interest Received',
                    'message' => "{$actor->name} has sent you an interest.",
                    'url' => route('interests.received'),
                    'tone' => 'brand',
                    'icon' => 'heart',
                ],
                [
                    'title' => 'Profile Viewed',
                    'message' => "{$actor->name} viewed your profile.",
                    'url' => route('dashboard'),
                    'tone' => 'info',
                    'icon' => 'eye',
                ],
            ];

            foreach ($notifications as $k => $data) {
                Notification::create([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\Notifications\\'.($k === 0 ? 'InterestReceivedNotification' : 'ProfileViewedNotification'),
                    'notifiable_type' => User::class,
                    'notifiable_id' => $member->id,
                    'data' => $data + [
                        'actor_id' => $actor->id,
                        'actor_name' => $actor->name,
                        'actor_photo' => $actor->photoUrl(),
                    ],
                    'read_at' => $k === 1 && $i % 3 === 0 ? now()->subDay() : null,
                    'created_at' => now()->subHours(rand(2, 24 * 10)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
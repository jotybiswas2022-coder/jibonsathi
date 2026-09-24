<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Interest;
use App\Models\MatchRecord;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private MatchingService $matcher,
        private ConversationService $conversations,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $user->loadMissing(['profile', 'primaryPhoto', 'partnerPreference', 'education', 'occupation']);

        $matches = MatchRecord::query()->where('user_id', $user->id);

        return [
            'completion' => $user->completion(),
            'profile_status' => $user->profile?->profile_status ?? 'pending',
            'verification_status' => $user->profile?->verification_status ?? 'unverified',
            'missing' => $this->missingSections($user),
            'stats' => [
                'new_matches' => (clone $matches)->where('created_at', '>=', now()->subDays(7))->count(),
                'total_matches' => $matches->count(),
                'high_compatibility' => (clone $matches)->where('match_percentage', '>=', 75)->count(),
                'received_interests' => $user->receivedInterests()->where('status', Interest::STATUS_PENDING)->count(),
                'sent_interests' => $user->sentInterests()->count(),
                'accepted_interests' => $user->sentInterests()->where('status', Interest::STATUS_ACCEPTED)->count()
                    + $user->receivedInterests()->where('status', Interest::STATUS_ACCEPTED)->count(),
                'profile_views' => $user->profileViews()->count(),
                'shortlisted' => Favorite::query()->where('user_id', $user->id)->count(),
                'unread_messages' => $this->conversations->unreadCount($user),
                'active_conversations' => Conversation::query()->forUser($user)->count(),
            ],
            'recent_viewers' => ProfileView::query()
                ->where('user_id', $user->id)
                ->with(['viewer.profile', 'viewer.primaryPhoto'])
                ->latest('viewed_at')
                ->limit(5)
                ->get(),
            'recent_interests' => $user->receivedInterests()
                ->with(['sender.profile', 'sender.primaryPhoto'])
                ->latest()
                ->limit(4)
                ->get(),
            'notifications' => $user->notifications()->latest()->limit(5)->get(),
            'recommended' => $this->recommended($user, 6),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function recommended(User $user, int $limit = 6): Collection
    {
        $recommended = MatchRecord::query()
            ->where('user_id', $user->id)
            ->with(['matchedUser.profile', 'matchedUser.primaryPhoto', 'matchedUser.education', 'matchedUser.occupation', 'matchedUser.lifestyleDetail', 'matchedUser.partnerPreference'])
            ->orderByDesc('match_percentage')
            ->limit($limit)
            ->get()
            ->map(fn (MatchRecord $match) => $match->matchedUser)
            ->filter(fn (?User $candidate) => $candidate && $candidate->isActive() && ! $user->blocksWith($candidate));

        if ($recommended->isNotEmpty()) {
            return $recommended->values();
        }

        // Fall back to a live computation before the matcher has run.
        return app(DiscoveryService::class)
            ->buildQuery($user, [])
            ->limit($limit)
            ->get();
    }

    /**
     * Sections that still need attention, used for the completion checklist.
     *
     * @return list<array{key: string, label: string, url: string, done: bool}>
     */
    public function missingSections(User $user): array
    {
        $user->loadMissing(['profile', 'education', 'occupation', 'familyDetail', 'lifestyleDetail', 'partnerPreference', 'primaryPhoto']);

        $sections = [
            [
                'key' => 'photo',
                'label' => 'Upload a profile photo',
                'url' => route('settings.photos', absolute: false),
                'done' => (bool) $user->primaryPhoto,
            ],
            [
                'key' => 'basic',
                'label' => 'Complete your basic information',
                'url' => route('settings.profile', absolute: false),
                'done' => filled($user->profile?->date_of_birth) && filled($user->profile?->religion) && filled($user->profile?->district),
            ],
            [
                'key' => 'about',
                'label' => 'Write a short about me',
                'url' => route('settings.profile', absolute: false),
                'done' => filled($user->profile?->about_me),
            ],
            [
                'key' => 'career',
                'label' => 'Add education & career',
                'url' => route('settings.career', absolute: false),
                'done' => filled($user->education?->level) || filled($user->occupation?->designation),
            ],
            [
                'key' => 'family',
                'label' => 'Share family details',
                'url' => route('settings.family', absolute: false),
                'done' => filled($user->familyDetail?->family_type),
            ],
            [
                'key' => 'lifestyle',
                'label' => 'Add lifestyle & interests',
                'url' => route('settings.lifestyle', absolute: false),
                'done' => $user->lifestyleDetail && $user->lifestyleDetail->allInterests() !== [],
            ],
            [
                'key' => 'preference',
                'label' => 'Set partner preferences',
                'url' => route('settings.preference', absolute: false),
                'done' => filled($user->partnerPreference?->age_min) || filled($user->partnerPreference?->preferred_gender),
            ],
            [
                'key' => 'verification',
                'label' => 'Verify your profile',
                'url' => route('verification.index', absolute: false),
                'done' => $user->isVerifiedProfile(),
            ],
        ];

        return $sections;
    }
}

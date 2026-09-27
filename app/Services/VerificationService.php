<?php

namespace App\Services;

use App\Models\User;
use App\Models\Verification;
use App\Notifications\VerificationApprovedNotification;
use App\Notifications\VerificationRejectedNotification;
use App\Notifications\VerificationSubmittedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerificationService
{
    public function __construct(private ProfileService $profiles)
    {
    }

    /**
     * Create (or refresh) a profile identity verification request with a private document.
     */
    public function submitProfile(User $user, UploadedFile $document, string $documentType, ?string $note = null): Verification
    {
        if ($user->profile?->verification_status === 'verified') {
            throw ValidationException::withMessages(['document' => 'Your profile is already verified.']);
        }

        // Documents are stored on the private disk â€” never publicly reachable.
        $path = $document->store('verifications/'.$user->id, 'local');

        $verification = DB::transaction(function () use ($user, $path, $documentType, $note) {
            $user->verifications()
                ->where('type', Verification::TYPE_PROFILE)
                ->where('status', Verification::STATUS_PENDING)
                ->update(['status' => Verification::STATUS_REJECTED, 'admin_note' => 'Superseded by a newer request.']);

            $verification = Verification::create([
                'user_id' => $user->id,
                'type' => Verification::TYPE_PROFILE,
                'status' => Verification::STATUS_PENDING,
                'document_path' => $path,
                'document_type' => $documentType,
                'note' => $note,
            ]);

            $this->profiles->profileFor($user)->forceFill(['verification_status' => 'pending'])->save();

            return $verification;
        });

        foreach (User::admins()->get() as $admin) {
            $admin->notify(new VerificationSubmittedNotification($verification));
        }

        return $verification;
    }

    /**
     * The one check this page offers: whether a government ID was confirmed.
     *
     * @return array<string, mixed>
     */
    public function identityStatus(User $user): array
    {
        return [
            'label' => 'Profile identity',
            'value' => 'Government ID or passport check',
            'status' => $user->profile?->verification_status ?? 'unverified',
        ];
    }

    /**
     * Admin approval â€” updates the owning profile where relevant.
     */
    public function approve(Verification $verification, ?User $admin = null, ?string $note = null): Verification
    {
        DB::transaction(function () use ($verification, $admin, $note) {
            $verification->update([
                'status' => Verification::STATUS_APPROVED,
                'admin_note' => $note,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now(),
            ]);

            $user = $verification->user;

            if ($verification->type === Verification::TYPE_PROFILE) {
                $this->profiles->profileFor($user)->forceFill(['verification_status' => 'verified'])->save();
            }

            if ($verification->type === Verification::TYPE_PHONE) {
                $user->forceFill(['phone_verified_at' => now()])->save();
            }
        });

        $verification->user->notify(new VerificationApprovedNotification($verification->type, $note));

        return $verification->refresh();
    }

    public function reject(Verification $verification, ?User $admin = null, ?string $note = null): Verification
    {
        DB::transaction(function () use ($verification, $admin, $note) {
            $verification->update([
                'status' => Verification::STATUS_REJECTED,
                'admin_note' => $note,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now(),
            ]);

            if ($verification->type === Verification::TYPE_PROFILE) {
                $this->profiles->profileFor($verification->user)->forceFill(['verification_status' => 'rejected'])->save();
            }
        });

        $verification->user->notify(new VerificationRejectedNotification($verification->type, $note));

        return $verification->refresh();
    }
}

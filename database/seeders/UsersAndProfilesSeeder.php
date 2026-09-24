<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates the platform admin plus a diverse member base with full profiles,
 * photos (generated as deterministic SVG gradient avatars on the public disk),
 * education / career / family / lifestyle / partner-preference relations.
 *
 * All members share the password "password".
 */
class UsersAndProfilesSeeder extends Seeder
{
    private const PASSWORD = 'password';

    private const RELIGIONS = ['islam', 'hinduism', 'christianity', 'buddhism', 'other'];

    private const MARITAL = ['never_married', 'never_married', 'never_married', 'divorced', 'widowed'];

    private const EDUCATION = ['bachelor', 'masters', 'engineering', 'medical', 'diploma', 'phd'];

    private const EMPLOYMENT = ['private', 'government', 'business', 'freelancer', 'self_employed'];

    private const INCOME = ['25k_50k', '50k_100k', '100k_200k', '200k_500k'];

    private const FAMILY_TYPE = ['nuclear', 'joint'];

    private const FAMILY_STATUS = ['modest', 'middle_class', 'upper_middle_class'];

    private const DIET = ['halal', 'non_vegetarian', 'vegetarian', 'eggetarian'];

    private const HEADLINES = [
        'Software engineer who loves quiet evenings',
        'Doctor by profession, poet by heart',
        'Teacher shaping tomorrow\u2019s leaders',
        'Entrepreneur with a family-first mindset',
        'Civil engineer, cricket enthusiast',
        'Architect who loves to travel',
        'Banker with a passion for cooking',
        'Journalist covering human stories',
        'Designer with a gentle soul',
        'Accountant, weekend hiker',
        'Government officer with simple values',
        'Nurse with an endless smile',
    ];

    private const ABOUT_ME = [
        'I value honesty, family and a calm partnership. Looking for someone who is understanding and ready to grow together.',
        'Simple, God-fearing person who enjoys long conversations, reading and spending time with family.',
        'I believe marriage is a friendship built with respect. Seeking a caring, compatible life partner.',
        'Warm-hearted, traditional yet open-minded. Enjoy travel, food and quiet walks.',
        'Faithful, grounded and optimistic. Hoping to find my best friend for life.',
    ];

    private const GROOM_NAMES = [
        'Arif Hasan', 'Tanvir Ahmed', 'Sabbir Rahman', 'Imran Kabir', 'Farhan Islam', 'Rakib Chowdhury',
        'Shakib Mahmud', 'Nafiul Karim', 'Ayan Siddique', 'Zubair Alam', 'Rezaul Haque', 'Mahdi Hasan',
        'Tariq Jaman', 'Asif Iqbal', 'Sumon Das', 'Rubel Ahmed', 'Kamrul Islam', 'Jahid Hossain',
    ];

    private const BRIDE_NAMES = [
        'Nusrat Jahan', 'Farzana Akter', 'Mehjabin Rahman', 'Sadia Islam', 'Tasnim Chowdhury', 'Rumana Haque',
        'Anika Karim', 'Mim Rahman', 'Sabina Yasmin', 'Nabila Ahmed', 'Eva Siddique', 'Rima Das',
        'Tania Begum', 'Sharmin Akter', 'Jannatul Ferdous', 'Lamia Rahman', 'Ishrat Jahan', 'Puja Roy',
    ];

    /** @var array<int, User> */
    public static array $members = [];

    public function run(): void
    {
        $this->command?->info('Seeding admin account…');
        $admin = $this->createAdmin();

        $this->command?->info('Seeding 36 members with full profiles…');
        $members = [];
        foreach (self::GROOM_NAMES as $i => $name) {
            $members[] = $this->createMember($name, 'male', $i);
        }
        foreach (self::BRIDE_NAMES as $i => $name) {
            $members[] = $this->createMember($name, 'female', $i);
        }

        static::$members = $members;

        $this->command?->info('Done. Members seeded: '.count($members).' + 1 admin.');
    }

    private function createAdmin(): User
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@jibonsathi.local'],
            [
                'name' => 'Jibon Sathi Admin',
                'username' => 'admin',
                'phone' => '01700000000',
                'email_verified_at' => now(),
                'password' => Hash::make(self::PASSWORD),
                'is_admin' => true,
                'status' => User::STATUS_ACTIVE,
                'last_active_at' => now(),
            ]
        );

        if (! $admin->profile()->exists()) {
            $admin->profile()->create([
                'gender' => 'male',
                'date_of_birth' => now()->subYears(32)->format('Y-m-d'),
                'height_cm' => 175,
                'marital_status' => 'never_married',
                'religion' => 'islam',
                'mother_tongue' => 'Bengali',
                'country' => 'bangladesh',
                'division' => 'Dhaka',
                'district' => 'Dhaka',
                'city' => 'Dhaka',
                'about_me' => 'Platform administrator for Jibon Sathi.',
                'headline' => 'Administrator',
                'profile_completion' => 100,
                'profile_status' => 'approved',
                'verification_status' => 'verified',
                'approved_at' => now()->subMonths(6),
                'profile_visibility' => 'private',
            ]);
        }

        return $admin;
    }

    private function createMember(string $name, string $gender, int $index): User
    {
        $age = 24 + ($index % 10); // 24–33
        $slug = Str::slug($name);
        $username = $slug.$index;

        $divisionNames = array_keys(\App\Support\Reference::divisions());
        $division = $divisionNames[$index % count($divisionNames)];
        $districts = \App\Support\Reference::divisions()[$division];
        $district = $districts[$index % count($districts)];

        $user = User::firstOrCreate(
            ['email' => "{$slug}{$index}@example.com"],
            [
                'name' => $name,
                'username' => $username,
                'phone' => '018'.str_pad((string) (10000000 + $index * 137), 9, '0', STR_PAD_LEFT),
                'email_verified_at' => now()->subMonths(rand(1, 10)),
                'password' => Hash::make(self::PASSWORD),
                'is_admin' => false,
                'status' => User::STATUS_ACTIVE,
                'last_active_at' => now()->subMinutes(rand(5, 60 * 24 * 14)),
            ]
        );

        if ($user->profile()->exists()) {
            return $user;
        }

        $isApproved = $index % 4 !== 3; // 3 in 4 approved, rest pending
        $verified = $isApproved && $index % 5 !== 4; // most approved are verified
        $religion = self::RELIGIONS[$index % count(self::RELIGIONS)];
        $marital = self::MARITAL[$index % count(self::MARITAL)];
        $education = self::EDUCATION[$index % count(self::EDUCATION)];
        $employment = self::EMPLOYMENT[$index % count(self::EMPLOYMENT)];
        $income = self::INCOME[$index % count(self::INCOME)];
        $familyType = self::FAMILY_TYPE[$index % count(self::FAMILY_TYPE)];
        $familyStatus = self::FAMILY_STATUS[$index % count(self::FAMILY_STATUS)];
        $diet = self::DIET[$index % count(self::DIET)];

        $user->profile()->create([
            'gender' => $gender,
            'date_of_birth' => now()->subYears($age)->subMonths($index % 12)->format('Y-m-d'),
            'height_cm' => ($gender === 'male' ? 165 : 152) + ($index % 14),
            'marital_status' => $marital,
            'religion' => $religion,
            'mother_tongue' => 'Bengali',
            'country' => 'bangladesh',
            'division' => $division,
            'district' => $district,
            'city' => $district,
            'about_me' => self::ABOUT_ME[$index % count(self::ABOUT_ME)],
            'headline' => self::HEADLINES[$index % count(self::HEADLINES)],
            'profile_completion' => $isApproved ? 100 : rand(45, 75),
            'profile_status' => $isApproved ? 'approved' : 'pending',
            'verification_status' => $verified ? 'verified' : ($isApproved ? 'unverified' : 'pending'),
            'approved_at' => $isApproved ? now()->subMonths(rand(1, 9)) : null,
            'profile_visibility' => 'members',
        ]);

        $user->educations()->create([
            'level' => $education,
            'degree' => ucfirst($education).' Degree',
            'institution' => ['University of Dhaka', 'BUET', 'NSU', 'BRAC University', 'Rajshahi University'][$index % 5],
            'field_of_study' => ['Computer Science', 'Business Administration', 'Medicine', 'Civil Engineering', 'English Literature'][$index % 5],
            'start_year' => 2012 + ($index % 6),
            'end_year' => 2016 + ($index % 6),
            'is_highest' => true,
        ]);

        $user->occupations()->create([
            'designation' => ['Software Engineer', 'Teacher', 'Doctor', 'Bank Officer', 'Entrepreneur', 'Accountant'][$index % 6],
            'company' => ['Grameenphone', 'BRAC Bank', 'Square Group', 'bKash', 'Own Business', 'UNDP'][$index % 6],
            'employment_type' => $employment,
            'income_range' => $income,
            'work_location' => $district,
            'is_current' => true,
        ]);

        $user->familyDetail()->create([
            'family_type' => $familyType,
            'family_status' => $familyStatus,
            'father_occupation' => ['Retired Government Officer', 'Businessman', 'Teacher', 'Engineer'][$index % 4],
            'mother_occupation' => ['Homemaker', 'Teacher', 'Doctor', 'Bank Officer'][$index % 4],
            'brothers' => $index % 3,
            'sisters' => ($index + 1) % 3,
            'family_income_range' => self::INCOME[($index + 1) % count(self::INCOME)],
            'about_family' => 'A warm, values-driven family that believes in mutual respect and togetherness.',
        ]);

        $user->lifestyleDetail()->create([
            'diet' => $diet,
            'smoking' => $index % 7 === 0 ? 'occasionally' : 'never',
            'drinking' => $index % 11 === 0 ? 'occasionally' : 'never',
            'hobbies' => array_values(array_slice(\App\Support\Reference::hobbies(), ($index * 2) % 14, 3)),
            'interests' => array_values(array_slice(\App\Support\Reference::interests(), ($index * 3) % 12, 3)),
            'about_lifestyle' => 'Family-oriented with a balanced routine — work, prayer, and time with loved ones.',
        ]);

        $user->partnerPreference()->create([
            'preferred_gender' => $gender === 'male' ? 'female' : 'male',
            'age_min' => 21,
            'age_max' => 32,
            'height_min_cm' => $gender === 'male' ? 150 : 165,
            'height_max_cm' => $gender === 'male' ? 170 : 185,
            'preferred_country' => 'bangladesh',
            'preferred_division' => $division,
            'religions' => [$religion],
            'marital_statuses' => ['never_married'],
            'education_level' => $education,
            'diet' => $diet,
            'smoking' => 'never',
            'drinking' => 'never',
            'notes' => 'Looking for a kind, family-minded partner.',
        ]);

        // Two generated gradient-avatar photos per member (approved).
        $this->seedPhoto($user, 'primary');
        $this->seedPhoto($user, 'secondary');

        return $user;
    }

    private function seedPhoto(User $user, string $slot): void
    {
        $path = "seed-photos/{$user->username}-{$slot}.svg";

        if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $this->gradientAvatarSvg($user->name.' '.$slot));
        }

        $user->photos()->firstOrCreate(
            ['path' => $path],
            [
                'is_primary' => $slot === 'primary',
                'sort_order' => $slot === 'primary' ? 0 : 1,
                'status' => 'approved',
            ]
        );
    }

    private function gradientAvatarSvg(string $seed): string
    {
        $hash = md5($seed);
        $hue = hexdec(substr($hash, 0, 4)) % 360;
        $hue2 = ($hue + 42) % 360;

        $words = preg_split('/\s+/', trim($seed)) ?: [];
        $initials = strtoupper(mb_substr($words[0] ?? 'J', 0, 1));
        if (count($words) > 1) {
            $initials .= strtoupper(mb_substr($words[count($words) - 1], 0, 1));
        } elseif (mb_strlen($words[0] ?? '') > 1) {
            $initials = strtoupper(mb_substr($words[0], 0, 2));
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="hsl({$hue},58%,44%)"/>
      <stop offset="100%" stop-color="hsl({$hue2},62%,58%)"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="url(#g)"/>
  <circle cx="320" cy="80" r="110" fill="#ffffff" fill-opacity="0.10"/>
  <circle cx="70" cy="330" r="90" fill="#ffffff" fill-opacity="0.08"/>
  <text x="50%" y="50%" dy="0.35em" text-anchor="middle"
        font-family="Inter, 'Segoe UI', Arial, sans-serif"
        font-size="150" font-weight="600" fill="#ffffff" fill-opacity="0.96">{$initials}</text>
</svg>
SVG;
    }
}
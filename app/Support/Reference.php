<?php

namespace App\Support;

class Reference
{
    /** @return array<string, string> */
    public static function genders(): array
    {
        return [
            'male' => 'Groom (Male)',
            'female' => 'Bride (Female)',
        ];
    }

    /** @return array<string, string> */
    public static function religions(): array
    {
        return [
            'islam' => 'Islam',
            'hinduism' => 'Hinduism',
            'christianity' => 'Christianity',
            'buddhism' => 'Buddhism',
            'other' => 'Other',
        ];
    }

    /** @return array<string, string> */
    public static function maritalStatuses(): array
    {
        return [
            'never_married' => 'Never Married',
            'divorced' => 'Divorced',
            'widowed' => 'Widowed',
            'awaiting_divorce' => 'Awaiting Divorce',
        ];
    }

    /** @return array<string, string> */
    public static function educationLevels(): array
    {
        return [
            'ssc' => 'Secondary (SSC)',
            'hsc' => 'Higher Secondary (HSC)',
            'diploma' => 'Diploma',
            'bachelor' => "Bachelor's Degree",
            'masters' => "Master's Degree",
            'phd' => 'PhD / Doctorate',
            'medical' => 'MBBS / Medical',
            'engineering' => 'B.Sc. Engineering',
            'other' => 'Other',
        ];
    }

    /** @return array<string, string> */
    public static function incomeRanges(): array
    {
        return [
            'below_25k' => 'Below ৳25,000',
            '25k_50k' => '৳25,000 - ৳50,000',
            '50k_100k' => '৳50,000 - ৳1,00,000',
            '100k_200k' => '৳1,00,000 - ৳2,00,000',
            '200k_500k' => '৳2,00,000 - ৳5,00,000',
            'above_500k' => 'Above ৳5,00,000',
            'prefer_not_to_say' => 'Prefer not to say',
        ];
    }

    /** @return array<string, string> */
    public static function diets(): array
    {
        return [
            'vegetarian' => 'Vegetarian',
            'non_vegetarian' => 'Non-Vegetarian',
            'halal' => 'Halal',
            'eggetarian' => 'Eggetarian',
            'vegan' => 'Vegan',
        ];
    }

    /** @return array<string, string> */
    public static function smoking(): array
    {
        return [
            'never' => 'Never',
            'occasionally' => 'Occasionally',
            'regularly' => 'Regularly',
            'trying_to_quit' => 'Trying to quit',
        ];
    }

    /** @return array<string, string> */
    public static function drinking(): array
    {
        return [
            'never' => 'Never',
            'occasionally' => 'Occasionally',
            'regularly' => 'Regularly',
        ];
    }

    /** @return array<string, string> */
    public static function familyTypes(): array
    {
        return [
            'nuclear' => 'Nuclear Family',
            'joint' => 'Joint Family',
        ];
    }

    /** @return array<string, string> */
    public static function familyStatuses(): array
    {
        return [
            'modest' => 'Modest',
            'middle_class' => 'Middle Class',
            'upper_middle_class' => 'Upper Middle Class',
            'affluent' => 'Affluent',
        ];
    }

    /** @return array<string, string> */
    public static function employmentTypes(): array
    {
        return [
            'government' => 'Government Service',
            'private' => 'Private Service',
            'business' => 'Business',
            'freelancer' => 'Freelancer',
            'self_employed' => 'Self Employed',
            'student' => 'Student',
            'not_working' => 'Not Working',
        ];
    }

    /** @return array<string, string> */
    public static function profileStatuses(): array
    {
        return [
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'suspended' => 'Suspended',
        ];
    }

    /** @return array<string, string> */
    public static function userStatuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended',
            'pending' => 'Pending',
        ];
    }

    /** @return array<string, string> */
    public static function verificationStatuses(): array
    {
        return [
            'unverified' => 'Unverified',
            'pending' => 'Pending Review',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];
    }

    /** @return array<string, string> */
    public static function countries(): array
    {
        return [
            'bangladesh' => 'Bangladesh',
            'india' => 'India',
            'pakistan' => 'Pakistan',
            'nepal' => 'Nepal',
            'sri_lanka' => 'Sri Lanka',
            'uae' => 'United Arab Emirates',
            'saudi_arabia' => 'Saudi Arabia',
            'qatar' => 'Qatar',
            'malaysia' => 'Malaysia',
            'singapore' => 'Singapore',
            'united_kingdom' => 'United Kingdom',
            'united_states' => 'United States',
            'canada' => 'Canada',
            'australia' => 'Australia',
            'other' => 'Other',
        ];
    }

    /** @return array<string, list<string>> */
    public static function divisions(): array
    {
        return [
            'Dhaka' => ['Dhaka', 'Gazipur', 'Narayanganj', 'Tangail', 'Kishoreganj', 'Manikganj', 'Munshiganj', 'Narsingdi', 'Faridpur', 'Gopalganj', 'Madaripur', 'Rajbari', 'Shariatpur'],
            'Chattogram' => ['Chattogram', "Cox's Bazar", 'Cumilla', 'Brahmanbaria', 'Chandpur', 'Feni', 'Lakshmipur', 'Noakhali', 'Khagrachhari', 'Rangamati', 'Bandarban'],
            'Rajshahi' => ['Rajshahi', 'Bogura', 'Pabna', 'Sirajganj', 'Natore', 'Naogaon', 'Chapainawabganj', 'Joypurhat'],
            'Khulna' => ['Khulna', 'Jashore', 'Kushtia', 'Jhenaidah', 'Magura', 'Meherpur', 'Narail', 'Chuadanga', 'Satkhira', 'Bagerhat'],
            'Barishal' => ['Barishal', 'Patuakhali', 'Bhola', 'Pirojpur', 'Barguna', 'Jhalokati'],
            'Sylhet' => ['Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj'],
            'Rangpur' => ['Rangpur', 'Dinajpur', 'Gaibandha', 'Kurigram', 'Lalmonirhat', 'Nilphamari', 'Panchagarh', 'Thakurgaon'],
            'Mymensingh' => ['Mymensingh', 'Jamalpur', 'Netrokona', 'Sherpur'],
        ];
    }

    /** @return list<string> */
    public static function divisionNames(): array
    {
        return array_keys(self::divisions());
    }

    /** @return list<string> */
    public static function allDistricts(): array
    {
        return array_merge(...array_values(self::divisions()));
    }

    /** @return list<string> */
    public static function hobbies(): array
    {
        return [
            'Travelling', 'Cooking', 'Reading Books', 'Music', 'Cricket', 'Football',
            'Watching Movies', 'Photography', 'Fitness & Gym', 'Art & Painting',
            'Gardening', 'Technology', 'Fashion', 'Volunteering', 'Writing',
            'Gaming', 'Hiking', 'Poetry', 'Calligraphy', 'Cycling',
        ];
    }

    /** @return list<string> */
    public static function interests(): array
    {
        return [
            'Family Values', 'Career Growth', 'Spirituality', 'Outdoor Activities',
            'Food Exploration', 'Community Service', 'Entrepreneurship', 'Pets',
            'Languages', 'History', 'Science', 'Crafts', 'Dance', 'Singing',
            'Movies & Series', 'Board Games', 'Finance', 'Architecture',
        ];
    }

    /**
     * Resolve a stored option key into its human label.
     */
    public static function label(?string $value, string $group): string
    {
        if (blank($value)) {
            return '—';
        }

        $source = match ($group) {
            'gender' => self::genders(),
            'religion' => self::religions(),
            'marital_status' => self::maritalStatuses(),
            'education' => self::educationLevels(),
            'income' => self::incomeRanges(),
            'diet' => self::diets(),
            'smoking' => self::smoking(),
            'drinking' => self::drinking(),
            'family_type' => self::familyTypes(),
            'family_status' => self::familyStatuses(),
            'employment' => self::employmentTypes(),
            'country' => self::countries(),
            default => [],
        };

        return $source[$value] ?? ucwords(str_replace('_', ' ', $value));
    }
}

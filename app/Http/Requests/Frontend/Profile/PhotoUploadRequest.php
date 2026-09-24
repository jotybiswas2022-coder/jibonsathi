<?php

namespace App\Http\Requests\Frontend\Profile;

use Illuminate\Foundation\Http\FormRequest;

class PhotoUploadRequest extends FormRequest
{
    /** Hard cap on gallery size per member. */
    public const MAX_PHOTOS = 8;

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'photo' => self::photoRules(),
            'make_primary' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Secure image validation shared by the wizard, settings and admin uploads.
     *
     * @return list<string>
     */
    public static function photoRules(): array
    {
        return [
            'required',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:4096', // 4 MB
            'dimensions:min_width=200,min_height=200,max_width=6000,max_height=6000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.max' => 'Photos must be smaller than 4 MB.',
            'photo.mimes' => 'Only JPG, PNG and WEBP images are allowed.',
            'photo.dimensions' => 'Please upload an image of at least 200x200 pixels.',
        ];
    }

    public function withinGalleryLimit(): bool
    {
        return $this->user()->photos()->count() < self::MAX_PHOTOS;
    }
}

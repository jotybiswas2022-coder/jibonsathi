<?php

namespace App\Http\Requests\Frontend\Settings;

use Illuminate\Foundation\Http\FormRequest;

class AccountActionRequest extends FormRequest
{
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
            'password' => ['required', 'current_password'],
            'confirm' => ['required', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.current_password' => 'Please confirm your password to continue.',
            'confirm.accepted' => 'Please tick the confirmation box to continue.',
        ];
    }
}

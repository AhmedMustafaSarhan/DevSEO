<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'subject' => ['required', 'string', 'min:5', 'max:200'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'region' => ['required', 'string', 'in:EG,US,INTL'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your name.',
            'name.min' => 'Name must be at least 3 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'subject.required' => 'Subject is required.',
            'subject.min' => 'Subject must be at least 5 characters.',
            'message.required' => 'Message cannot be empty.',
            'message.min' => 'Message must be at least 20 characters.',
            'region.required' => 'Please select your region.',
            'region.in' => 'Invalid region selected.',
        ];
    }

    /**
     * Get the locale of the request.
     */
    public function getLocale(): string
    {
        return $this->header('Accept-Language') ?? config('app.locale', 'en');
    }
}

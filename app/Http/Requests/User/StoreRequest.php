<?php

namespace App\Http\Requests\User;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public const ROLE_REQUEST_MAPPING = [
        Role::CANDIDATE->value => Candidate\StoreRequest::class,
        Role::COMPANY->value => Company\StoreRequest::class,
        Role::ADMIN->value => Admin\StoreRequest::class,
        Role::MODERATOR->value => Moderator\StoreRequest::class,
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Role-specific authorization handled in resolved requests
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'passwordConfirm' => 'required|same:password',
            'data' => 'required|array',
            'role' => ['required', 'string', Rule::enum(Role::class)],
        ];
    }
}

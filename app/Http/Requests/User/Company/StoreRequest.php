<?php

namespace App\Http\Requests\User\Company;

class StoreRequest extends \App\Http\Requests\User\StoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'data.company_name' => 'required|string|max:255',
            'data.tax_id' => 'required|string|max:50',
            'data.website' => 'nullable|url',
        ]);
    }
}

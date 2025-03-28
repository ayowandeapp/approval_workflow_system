<?php

namespace App\Http\Requests;

use App\Enums\ActivationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApprovalFlowRequest extends FormRequest
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
        return [
            'approver_id' => [
                'sometimes',
                'exists:users,id',
            ],
            'level' => 'sometimes|integer|min:1',
            // 'is_active' => ['sometimes', Rule::enum(ActivationStatus::class)]
        ];
    }
}

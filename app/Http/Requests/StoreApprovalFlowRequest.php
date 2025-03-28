<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApprovalFlowRequest extends FormRequest
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
            // 'department_id' => 'required|exists:departments,id',
            'approver_id' => [
                'required',
                'exists:users,id',
                // Rule::unique('approval_flows')
                //     ->where('department_id', $this->input('department_id'))
                //     ->where('level', $this->input('level'))

            ],
            'level' => 'required|integer|min:1'
        ];
    }
}

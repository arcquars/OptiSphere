<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendProductsRequest extends FormRequest
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
        $warehouse_m_id = $this->input('warehouse_m_id') ?? $this->route('warehouse_m_id');
        $type = $this->input('type') ?? $this->route('type');
        dd($warehouse_m_id);
        return [
            'selectedBranchId' => 'required|exists:branches,id|check-send-products:' . $warehouse_m_id . ',' . $type,
            'transferNote' => 'nullable|string|max:5',
            'warehouse_m_id' => 'required'
        ];
    }
}

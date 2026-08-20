<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateUnitRequest
 */
class UpdateUnitRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $companyId = current_company_id();

        return [
            'name' => 'required|unique:units,name,'.$this->route('unit').',id,company_id,'.$companyId,
            'short_name' => 'required',
            'base_unit' => 'required',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePraticheRequest extends FormRequest
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
            'pratica_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pratiches', 'pratica_id')->ignore($this->pratiche->id),
            ],
            'Data_inserimento' => 'nullable|date',
            'Descrizione' => 'nullable|string',
            'Cliente' => 'nullable|string|max:255',
            'Agente' => 'nullable|string|max:255',
            'Segnalatore' => 'nullable|string|max:255',
            'Fonte' => 'nullable|string|max:255',
            'Tipo' => 'nullable|string|max:255',
            'Istituto_finanziario' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'pratica_id.required' => 'L\'ID pratica è obbligatorio.',
            'pratica_id.unique' => 'Questo ID pratica è già stato utilizzato.',
            'Data_inserimento.date' => 'La data di inserimento deve essere una data valida.',
        ];
    }
}

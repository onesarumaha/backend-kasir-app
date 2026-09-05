<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantResquest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'footer_receipt_text' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama toko/tenant wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.mimes' => 'Format logo yang diperbolehkan hanya: JPG, JPEG, PNG, WEBP.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
        ];
    }
}

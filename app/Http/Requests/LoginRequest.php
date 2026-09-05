<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid ya.',

            'password.required' => 'Password wajib diisi yaa.',
        ];
    }

    /**
     * Hook validasi tambahan setelah rule dasar lolos
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $user = User::where('email', $this->email)->first();

            // 1. Cek Kredensial Email & Password
            if (!$user || !Hash::check($this->password, $user->password)) {
                $validator->errors()->add('email', 'Email atau password salah.');
                return;
            }

            // 2. Cek Status Aktif Tenant (jika user terikat dengan tenant)
            if ($user->tenant_id && $user->tenant && !$user->tenant->is_active) {
                $validator->errors()->add('email', 'Toko/Tenant Anda sedang dinonaktifkan. Silakan hubungi admin.');
            }
        });
    }
}

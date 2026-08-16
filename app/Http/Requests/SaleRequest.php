<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
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
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.qty' => [
                'required',
                'integer',
                'min:1',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'tax' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'payment' => [
                'required',
                'numeric',
                'min:0',
            ],

            'payment_method' => [
                'required',
                'string',
                'in:cash,transfer,qris,debit,credit',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Item transaksi wajib diisi.',
            'items.array' => 'Format item transaksi tidak valid.',
            'items.min' => 'Minimal ada satu item transaksi.',

            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan.',

            'items.*.qty.required' => 'Qty wajib diisi.',
            'items.*.qty.integer' => 'Qty harus berupa angka.',
            'items.*.qty.min' => 'Qty minimal 1.',

            'discount.numeric' => 'Discount harus berupa angka.',
            'discount.min' => 'Discount tidak boleh negatif.',

            'tax.numeric' => 'Tax harus berupa angka.',
            'tax.min' => 'Tax tidak boleh negatif.',

            'payment.required' => 'Pembayaran wajib diisi.',
            'payment.numeric' => 'Pembayaran harus berupa angka.',
            'payment.min' => 'Pembayaran tidak boleh negatif.',

            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ];
    }
}

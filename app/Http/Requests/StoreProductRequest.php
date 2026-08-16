<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                'unique:products,code',
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,barcode',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'minimum_stock' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'unit' => [
                'sometimes',
                'string',
                'max:255',
            ],

           'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'status' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',

            'code.required' => 'Kode produk wajib diisi.',
            'code.unique' => 'Kode produk sudah digunakan.',

            'barcode.unique' => 'Barcode sudah digunakan.',

            'name.required' => 'Nama produk wajib diisi.',

            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh kurang dari 0.',

            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh kurang dari 0.',

            'minimum_stock.integer' => 'Minimum stok harus berupa angka bulat.',
            'minimum_stock.min' => 'Minimum stok tidak boleh kurang dari 0.',

            'status.boolean' => 'Status harus berupa true atau false.',
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Toko Utama',
            'slug' => 'toko-utama',
            'is_active' => true,
        ]);
        $categories = [
            [
                'name' => 'Minuman',
                'description' => 'Kategori produk minuman.',
                'status' => true,
                'tenant_id'   => $tenant->id,
            ],
            [
                'name' => 'Makanan',
                'description' => 'Kategori produk  Makanan.',
                'status' => true,
                'tenant_id'   => $tenant->id,
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'tenant_id' => $category['tenant_id'],
                'name' => $category['name'],
                'description' => $category['description'],
                'status' => $category['status'],
            ]);
        }
    }
}

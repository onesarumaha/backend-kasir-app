<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Minuman',
                'description' => 'Kategori produk minuman.',
                'status' => true,
            ],
            [
                'name' => 'Makanan',
                'description' => 'Kategori produk  Makanan.',
                'status' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'description' => $category['description'],
                'status' => $category['status'],
            ]);
        }
    }
}

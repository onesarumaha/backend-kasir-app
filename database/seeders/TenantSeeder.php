<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'PT. CAHAYA JAKARTA',
                'slug' => 'cahayajakarta.com',
                'phone' => '0877776665555',
                'is_active' => true,
                'address' => 'Jakarta',
            ],
            [
                'name' => 'PT. Medan Merdeka ',
                'slug' => 'medanmerdeka.com',
                'phone' => '0877776665552',
                'is_active' => true,
                'address' => 'Medan',
            ],
        ];

        foreach ($tenants as $tenant) {
            Tenant::create([
                'name' => $tenant['name'],
                'slug' => $tenant['slug'],
                'phone' => $tenant['phone'],
                'is_active' => $tenant['is_active'],
                'address' => $tenant['address'],
            ]);
        }
    }
}

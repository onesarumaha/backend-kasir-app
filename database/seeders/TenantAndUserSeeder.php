<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Tenant Pertama (Toko Utama)
        $tenant = Tenant::create([
            'name' => 'Toko Kelontong Berkah',
            'slug' => Str::slug('Toko Kelontong Berkah'),
            'email' => 'berkah@toko.com',
            'phone' => '081234567890',
            'address' => 'Jl. Kebon Jeruk No. 12, Jakarta',
            'is_active' => true,
            'footer_receipt_text' => 'Terima kasih telah berbelanja di Toko Berkah!',
        ]);

        // 2. Buat Superadmin (Akses Semua Tenant)
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pos.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'tenant_id' => null, // Superadmin tidak terikat 1 tenant tertentu
        ]);

        // 3. Buat Admin Toko (Terikat Tenant)
        User::create([
            'name' => 'Admin Toko Berkah',
            'email' => 'admin@berkah.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'tenant_id' => $tenant->id,
        ]);

        // 4. Buat Kasir Toko (Terikat Tenant)
        User::create([
            'name' => 'Kasir Budi',
            'email' => 'kasir@berkah.com',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'tenant_id' => $tenant->id,
        ]);
    }
}

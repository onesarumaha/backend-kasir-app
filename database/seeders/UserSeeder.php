<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first();

        // Jaga-jaga jika TenantSeeder belum dijalankan atau tabel tenant masih kosong
        if (!$tenant) {
            $tenant = Tenant::create([
                'name'      => 'Toko Utama',
                'slug'      => 'toko-utama',
                'phone'     => '081234567890',
                'address'   => 'Jl. Utama No. 1',
                'is_active' => true,
            ]);
        }

        $users = [
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Admin',
                'email'     => 'admin@kasir.com',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
            ],
            [
                'tenant_id' => $tenant->id,
                'name'      => 'Kasir',
                'email'     => 'kasir@kasir.com',
                'password'  => Hash::make('password123'),
                'role'      => 'kasir',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']], 
                $userData
            );
        }
    }
}

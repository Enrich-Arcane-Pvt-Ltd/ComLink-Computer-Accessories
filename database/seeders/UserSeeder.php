<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@enricharcane.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'role' => 'super-admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'password' => Hash::make('Enrich@1qaz'),
            ]
        );

        if (! $superAdmin->customer_id) {
            $superAdmin->customer_id = 'VCUS'.str_pad($superAdmin->id, 5, '0', STR_PAD_LEFT);
            $superAdmin->save();
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@wiki-store.com'],
            [
                'first_name' => 'WikiStore',
                'last_name' => 'Admin',
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'password' => Hash::make('Admin@1qaz'),
            ]
        );
        if (! $admin->customer_id) {
            $admin->customer_id = 'VCUS'.str_pad($admin->id, 5, '0', STR_PAD_LEFT);
            $admin->save();
        }
    }
}

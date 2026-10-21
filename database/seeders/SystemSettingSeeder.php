<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::firstOrCreate(
            ['id' => 1],
            [
                'shop_name' => 'Wiki-Store',
                'shop_email' => 'innfo@wiki-store.com',
                'shop_phone' => '+94 76 636 9801',
                'shop_address' => "Colombo Road,\nNegombo 11500,\nSri Lanka.",
                'currency_symbol' => 'LKR',
                'location_lat' => '7.20806', // Example Latitude
                'location_long' => '79.8413652', // Example Longitude
                'social_links' => [
                    'facebook' => 'https://facebook.com/Wiki-Store',
                    'instagram' => 'https://instagram.com/Wiki-Store',
                    'whatsapp' => 'https://wa.me/94771234567',
                ],
            ]
        );
    }
}

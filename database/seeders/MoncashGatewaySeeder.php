<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoncashGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('gateways')->updateOrInsert(
            ['gateway_code' => 'moncash'],
            [
                'logo' => 'global/gateway/moncash.png',
                'name' => 'MonCash',
                'supported_currencies' => json_encode(['HTG']),
                'credentials' => json_encode([
                    'clientSecret' => 'MagcNMsaFPQGykUuVb_shNUjdE6hRgSnkPhqx8CoE1wwi-4mdIqqnhZeREZTdO6G',
                    'clientId' => '8cb8b0f7f04c8ad0b8a6e0fcefed7495',
                    'businessKey' => 'TTBKRFRVeDRibk51TVdzOSBOR1EyYzA5dlIyNTVkR2hXUjNnemNtUkdVRWhMWnowOQ==',
                    'mode' => 'sandbox',
                ]),
                'is_withdraw' => '0',
                'status' => 1,
                'updated_at' => now(),
            ]
        );
    }
}

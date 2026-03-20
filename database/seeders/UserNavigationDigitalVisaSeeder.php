<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserNavigationDigitalVisaSeeder extends Seeder
{
    public function run()
    {
        DB::table('user_navigations')->insert([
            'icon' => 'credit-card',
            'url' => 'user/reseller-digital-visa-cards',
            'type' => 'card',
            'name' => 'Digital VisaCard',
            'position' => 2,
            'translation' => null,
            'created_at' => null,
            'updated_at' => '2024-05-13 12:47:22',
        ]);
    }
}


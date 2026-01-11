<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::firstOrCreate([
            'name' => 'Bosna',
            'code' => 'ba',
            'user_id' => 1,
        ]);
        $country = Country::firstOrCreate([
            'name' => 'Ujedinjeno Kraljevstvo',
            'code' => 'uk',
            'user_id' => 1,
        ]);
        $country = Country::firstOrCreate([
            'name' => 'Hrvatska',
            'code' => 'hr',
            'user_id' => 1,
        ]);
        $country = Country::firstOrCreate([
            'name' => 'Njemačka',
            'code' => 'de',
            'user_id' => 1,
        ]);
    }
}

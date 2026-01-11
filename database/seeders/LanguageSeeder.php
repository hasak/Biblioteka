<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $language = Language::firstOrCreate([
            'name' => 'bosanski',
            'code' => 'bs',
            'user_id' => 1,
        ]);
        $language = Language::firstOrCreate([
            'name' => 'engleski',
            'code' => 'en',
            'user_id' => 1,
        ]);
        $language = Language::firstOrCreate([
            'name' => 'hrvatski',
            'code' => 'hr',
            'user_id' => 1,
        ]);
        $language = Language::firstOrCreate([
            'name' => 'njemački',
            'code' => 'de',
            'user_id' => 1,
        ]);
    }
}

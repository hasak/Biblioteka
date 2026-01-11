<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genre = Genre::firstOrCreate([
            'name' => 'Fantazija',
            'user_id' => 1,
        ]);
        $genre = Genre::firstOrCreate([
            'name' => 'SciFi',
            'user_id' => 1,
        ]);
    }
}

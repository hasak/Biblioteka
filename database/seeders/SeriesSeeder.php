<?php

namespace Database\Seeders;

use App\Models\Series;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $series = Series::firstOrCreate([
            'title' => 'Harry Potter',
            'author' => 'J.K. Rowling',
            'genre_id' => 1,
            'is_completed' => true,
            'user_id' => 1
        ]);
        $series = Series::firstOrCreate([
            'title' => 'Back to the Future',
            'author' => 'Spielberg',
            'genre_id' => 2,
            'is_completed' => false,
            'user_id' => 1
        ]);
    }
}

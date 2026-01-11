<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $book = Book::firstOrCreate([
            'title' => 'Harry Potter 1',
            'author' => 'J.K. Rowling',
            'series_id' => 1,
            'part_number' => 1,
            'publisher' => 'Hogwarts',
            'year' => '1990',
            'country_id' => 2,
            'language_id' => 2,
            'original_title' => 'Haris Lončar',
            'genre_id' => 1,
            'shelf_x' => 2,
            'shelf_y' => 1,
            'is_read' => true,
            'read_date' => '2015-06-30',
            'purchased_country_id' => 1,
            'purchased_city' => 'Bugojno',
            'purchased_date' => '2014-06-30',
            'isbn' => '123456789',
            'user_id' => 1,
        ]);
        $book = Book::firstOrCreate([
            'title' => 'Harry Potter 2',
            'author' => 'J.K. Rowling',
            'series_id' => 1,
            'part_number' => 2,
            'publisher' => 'Hogwarts',
            'year' => '1990',
            'country_id' => 2,
            'language_id' => 2,
            'original_title' => 'Haris Lončar',
            'genre_id' => 1,
            'shelf_x' => 2,
            'shelf_y' => 1,
            'is_read' => true,
            'purchased_country_id' => 1,
            'purchased_city' => 'Bugojno',
            'purchased_date' => '2014-06-30',
            'isbn' => '123456788',
            'user_id' => 1,
        ]);
        $book = Book::firstOrCreate([
            'title' => 'Harry Potter 3',
            'author' => 'J.K. Rowling',
            'series_id' => 1,
            'part_number' => 3,
            'publisher' => 'Hogwarts',
            'year' => '1990',
            'country_id' => 2,
            'language_id' => 2,
            'original_title' => 'Haris Lončar',
            'genre_id' => 1,
            'shelf_x' => 2,
            'shelf_y' => 1,
            'is_read' => false,
            'read_date' => '2015-06-30',
            'purchased_country_id' => 1,
            'purchased_city' => 'Bugojno',
            'purchased_date' => '2014-06-30',
            'isbn' => '123456787',
            'user_id' => 1,
        ]);
        $book = Book::firstOrCreate([
            'title' => 'BTTF1',
            'author' => 'Spielberg',
            'series_id' => 2,
            'part_number' => 1,
            'publisher' => 'Warner Bros',
            'year' => '1985',
            'country_id' => 2,
            'language_id' => 2,
            'original_title' => 'Povratak u Budućnost',
            'genre_id' => 2,
            'shelf_x' => 4,
            'shelf_y' => 1,
            'is_read' => false,
            'read_date' => '2015-06-30',
            'purchased_country_id' => 3,
            'purchased_city' => 'Bugojno',
            'purchased_date' => '2013-06-30',
            'isbn' => '123456786',
            'user_id' => 1,
        ]);
        $book = Book::firstOrCreate([
            'title' => 'Train in the Snow',
            'author' => 'Mato Lovrak',
            'publisher' => 'Lasta',
            'year' => '1975',
            'country_id' => 1,
            'language_id' => 1,
            'original_title' => 'Vlak u snijegu',
            'genre_id' => 1,
            'shelf_x' => 5,
            'shelf_y' => 6,
            'is_read' => true,
            'read_date' => '2019-06-30',
            'purchased_country_id' => 1,
            'purchased_city' => 'Tešanj',
            'purchased_date' => '2003-06-30',
            'isbn' => '123456785',
            'user_id' => 1,
        ]);
    }
}

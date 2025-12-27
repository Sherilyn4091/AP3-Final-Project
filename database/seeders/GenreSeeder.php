<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        
        $genres = [
            ['genre_name' => 'Rock', 'is_active' => true],
            ['genre_name' => 'Pop', 'is_active' => true],
            ['genre_name' => 'Jazz', 'is_active' => true],
            ['genre_name' => 'Classical', 'is_active' => true],
            ['genre_name' => 'Blues', 'is_active' => true],
            ['genre_name' => 'R&B', 'is_active' => true],
            ['genre_name' => 'Country', 'is_active' => true],
            ['genre_name' => 'Folk', 'is_active' => true],
            ['genre_name' => 'Metal', 'is_active' => true],
            ['genre_name' => 'Indie', 'is_active' => true],
            ['genre_name' => 'Electronic', 'is_active' => true],
            ['genre_name' => 'Hip-Hop', 'is_active' => true],
        ];

        foreach ($genres as $genre) {
            if (!DB::table('genre')->where('genre_name', $genre['genre_name'])->exists()) {
                $genre['created_at'] = now();
                $genre['updated_at'] = now();
                DB::table('genre')->insert($genre);
            }
        }
    }
}
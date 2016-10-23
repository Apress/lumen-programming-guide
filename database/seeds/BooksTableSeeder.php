<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BooksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $authors = factory(App\Author::class, 10)->create();
        $authors->each(function ($author) {
            $author->ratings()->saveMany(
                factory(App\Rating::class, rand(20, 50))->make()
            );

            $booksCount = rand(1, 5);

            while ($booksCount > 0) {
                $book = factory(App\Book::class)->make();
                $author->books()->save($book);
                $book->ratings()->saveMany(
                    factory(App\Rating::class, rand(20, 50))->make()
                );
                $booksCount--;
            }
        });
    }
}

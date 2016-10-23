<?php

namespace Tests\App\Http\Controllers;

use TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;

class BundlesControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function show_should_return_a_valid_bundle()
    {
        $bundle = $this->bundleFactory();

        $this->get("/bundles/{$bundle->id}", ['Accept' => 'application/json']);
        $this->seeStatusCode(200);
        $body = $this->response->getData(true);

        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];

        // Check bundle properties exist in the response
        $this->assertEquals($bundle->id, $data['id']);
        $this->assertEquals($bundle->title, $data['title']);
        $this->assertEquals($bundle->title, $data['title']);
        $this->assertEquals(
            $bundle->description,
            $data['description']
        );
        $this->assertEquals(
            $bundle->created_at->toIso8601String(),
            $data['created']
        );
        $this->assertEquals(
            $bundle->updated_at->toIso8601String(),
            $data['updated']
        );

        // Check that book data is in the response
        $this->assertArrayHasKey('books', $data);
        $books = $data['books'];

        // Check that two books exist in the response
        $this->assertArrayHasKey('data', $books);
        $this->assertCount(2, $books['data']);

        // Verify keys for one book...
        $this->assertEquals(
            $bundle->books[0]->title,
            $books['data'][0]['title']
        );
        $this->assertEquals(
            $bundle->books[0]->description,
            $books['data'][0]['description']
        );
        $this->assertEquals(
            $bundle->books[0]->author->name,
            $books['data'][0]['author']
        );
        $this->assertEquals(
            $bundle->books[0]->created_at->toIso8601String(),
            $books['data'][0]['created']
        );
        $this->assertEquals(
            $bundle->books[0]->updated_at->toIso8601String(),
            $books['data'][0]['updated']
        );
    }

    /** @test **/
    public function addBook_should_add_a_book_to_a_bundle()
    {
        $bundle = factory(\App\Bundle::class)->create();
        $book = $this->bookFactory();

        // Bundle should not have any associated books yet
        $this->notSeeInDatabase('book_bundle', ['bundle_id' => $bundle->id]);

        $this->put("/bundles/{$bundle->id}/books/{$book->id}", [],
            ['Accept' => 'application/json']);

        $this->seeStatusCode(200);

        $dbBundle = \App\Bundle::with('books')->find($bundle->id);
        $this->assertCount(1, $dbBundle->books,
            'The bundle should have 1 associated book');

        $this->assertEquals(
            $dbBundle->books()->first()->id,
            $book->id
        );

        $body = $this->response->getData(true);

        $this->assertArrayHasKey('data', $body);
        // Ensure the book id is in the response.
        $this->assertArrayHasKey('books', $body['data']);
        $this->assertArrayHasKey('data', $body['data']['books']);

        // Make sure the book is in the response
        $books = $body['data']['books'];
        $this->assertEquals($book->id, $books['data'][0]['id']);
    }

    /** @test **/
    public function removeBook_should_remove_a_book_from_a_bundle()
    {
        $bundle = $this->bundleFactory(3);
        $book = $bundle->books()->first();

        $this->seeInDatabase('book_bundle', [
            'book_id' => $book->id,
            'bundle_id' => $bundle->id
        ]);

        $this->assertCount(3, $bundle->books);

        $this
            ->delete("/bundles/{$bundle->id}/books/{$book->id}")
            ->seeStatusCode(204)
            ->notSeeInDatabase('book_bundle', [
                'book_id' => $book->id,
                'bundle_id' => $bundle->id
            ]);

        $dbBundle = \App\Bundle::find($bundle->id);
        $this->assertCount(2, $dbBundle->books);
    }
}

<?php

namespace Tests\App\Transformer;

use TestCase;
use App\Transformer\AuthorTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthorTransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->subject = new AuthorTransformer();
    }
    /** @test **/
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(AuthorTransformer::class, $this->subject);
    }

    /** @test **/
    public function it_can_transform_an_author()
    {
        $author = factory(\App\Author::class)->create();

        $author->ratings()->save(
            factory(\App\Rating::class)->make(['value' => 5])
        );

        $author->ratings()->save(
            factory(\App\Rating::class)->make(['value' => 3])
        );

        $actual = $this->subject->transform($author);

        $this->assertEquals($author->id, $actual['id']);
        $this->assertEquals($author->name, $actual['name']);
        $this->assertEquals($author->gender, $actual['gender']);
        $this->assertEquals($author->biography, $actual['biography']);
        $this->assertEquals($author->created_at->toIso8601String(), $actual['created']);
        $this->assertEquals($author->updated_at->toIso8601String(), $actual['created']);

        // Rating
        $this->assertArrayHasKey('rating', $actual);
        $this->assertInternalType('array', $actual['rating']);
        $this->assertEquals(4, $actual['rating']['average']);
        $this->assertEquals(5, $actual['rating']['max']);
        $this->assertEquals(80, $actual['rating']['percent']);
        $this->assertEquals(2, $actual['rating']['count']);
    }

    /** @test **/
    public function it_can_transform_related_books()
    {
        $book = $this->bookFactory();
        $author = $book->author;

        $data = $this->subject->includeBooks($author);
        $this->assertInstanceOf(\League\Fractal\Resource\Collection::class, $data);
    }
}

<?php

namespace Tests\App\Http\Controllers;

use TestCase;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthorsControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function index_responds_with_200_status_code()
    {
        $this->get('/authors')->seeStatusCode(Response::HTTP_OK);
    }

    /** @test **/
    public function index_should_return_a_collection_of_records()
    {
        $authors = factory(\App\Author::class, 2)->create();

        $this->get('/authors', ['Accept' => 'application/json']);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(2, $body['data']);

        foreach ($authors as $author) {
            $this->seeJson([
                'id' => $author->id,
                'name' => $author->name,
                'gender' => $author->gender,
                'biography' => $author->biography,
                'created' => $author->created_at->toIso8601String(),
                'updated' => $author->updated_at->toIso8601String(),
            ]);
        }
    }

    /** @test **/
    public function show_should_return_a_valid_author()
    {
        $book = $this->bookFactory();
        $author = $book->author;

        $this->get("/authors/{$author->id}", ['Accept' => 'application/json']);
        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $this->seeJson([
            'id' => $author->id,
            'name' => $author->name,
            'gender' => $author->gender,
            'biography' => $author->biography,
            'created' => $author->created_at->toIso8601String(),
            'updated' => $author->updated_at->toIso8601String(),
        ]);
    }

    /** @test **/
    public function show_should_fail_on_an_invalid_author()
    {
        $this->get('/authors/1234', ['Accept' => 'application/json']);
        $this->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this->seeJson([
            'message' => 'Not Found',
            'status' => Response::HTTP_NOT_FOUND
        ]);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('error', $body);
        $error = $body['error'];

        $this->assertEquals('Not Found', $error['message']);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $error['status']);
    }

    /** @test **/
    public function show_optionally_includes_books()
    {
        $book = $this->bookFactory();
        $author = $book->author;
     
        $this->get(
            "/authors/{$author->id}?include=books",
            ['Accept' => 'application/json']
        );

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('data', $data['books']);
        $this->assertCount(1, $data['books']['data']);

        // See Author Data
        $this->seeJson([
            'id' => $author->id,
            'name' => $author->name,
        ]);

        // Test included book Data (the first record)
        $actual = $data['books']['data'][0];
        $this->assertEquals($book->id, $actual['id']);
        $this->assertEquals($book->title, $actual['title']);
        $this->assertEquals($book->description, $actual['description']);
        $this->assertEquals(
            $book->created_at->toIso8601String(),
            $actual['created']
        );
        $this->assertEquals(
            $book->updated_at->toIso8601String(),
            $actual['updated']
        );
    }

    /** @test **/
    public function store_can_create_a_new_author()
    {
        $postData = [
            'name' => 'H. G. Wells',
            'gender' => 'male',
            'biography' => 'Prolific Science-Fiction Writer',
        ];

        $this->post('/authors', $postData, ['Accept' => 'application/json']);
     
        $this->seeStatusCode(201);
        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->seeJson($postData);

        $this->seeInDatabase('authors', $postData);
    }

    /** @test **/
    public function validation_validates_required_fields()
    {
        $author = factory(\App\Author::class)->create();
        $tests = [
            ['method' => 'post', 'url' => '/authors'],
            ['method' => 'put', 'url' => "/authors/{$author->id}"],
        ];

        foreach ($tests as $test) {
            $method = $test['method'];
            $this->{$method}($test['url'], [], ['Accept' => 'application/json']);
            $this->seeStatusCode(422);
            $data = $this->response->getData(true);

            $fields = ['name', 'gender', 'biography'];

            foreach ($fields as $field) {
                $this->assertArrayHasKey($field, $data);
                $this->assertEquals(["The {$field} field is required."], $data[$field]);
            }
        }
     }

    /** @test **/
    public function validation_invalidates_incorrect_gender_data()
    {
        foreach ($this->getValidationTestData() as $test) {
            $method = $test['method'];
            $test['data']['gender'] = 'unknown';
            $this->{$method}($test['url'], $test['data'], ['Accept' => 'application/json']);

            $this->seeStatusCode(422);

            $data = $this->response->getData(true);
            $this->assertCount(1, $data);
            $this->assertArrayHasKey('gender', $data);
            $this->assertEquals(
                ["Gender format is invalid: must equal 'male' or 'female'"],
                $data['gender']
            );
        }
    }

    /** @test **/
    public function validation_invalidates_name_when_name_is_just_too_long()
    {
        foreach ($this->getValidationTestData() as $test) {
            $method = $test['method'];
            $test['data']['name'] = str_repeat('a', 256);

            $this->{$method}($test['url'], $test['data'], ['Accept' => 'application/json']);

            $this->seeStatusCode(422);

            $data = $this->response->getData(true);
            $this->assertCount(1, $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertEquals(["The name may not be greater than 255 characters."], $data['name']);
        }
    }

    /** @test **/
    public function validation_is_valid_when_name_is_just_long_enough()
    {
        foreach ($this->getValidationTestData() as $test) {
            $method = $test['method'];
            $test['data']['name'] = str_repeat('a', 255);

            $this->{$method}($test['url'], $test['data'], ['Accept' => 'application/json']);

            $this->seeStatusCode($test['status']);
            $this->seeInDatabase('authors', $test['data']);
        }
    }

    /** @test **/
    public function store_returns_a_valid_location_header()
    {
        $postData = [
            'name' => 'H. G. Wells',
            'gender' => 'male',
            'biography' => 'Prolific Science-Fiction Writer'
        ];
     
        $this
            ->post('/authors', $postData,
                ['Accept' => 'application/json'])
            ->seeStatusCode(201);

        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);

        // Check the Location header
        $id = $data['data']['id'];
        $this->seeHeaderWithRegExp('Location', "#/authors/{$id}$#");
    }

    /** @test **/
    public function update_can_update_an_existing_author()
    {
        $author = factory(\App\Author::class)->create();

        $requestData = [
            'name' => 'New Author Name',
            'gender' => $author->gender === 'male' ? 'female' : 'male',
            'biography' => 'An updated biography',
        ];

        $this
            ->put(
                "/authors/{$author->id}",
                $requestData,
                ['Accept' => 'application/json']
            )
            ->seeStatusCode(200)
            ->seeJson($requestData)
            ->seeInDatabase('authors', [
                'name' => 'New Author Name'
            ])
            ->notSeeInDatabase('authors', [
                'name' => $author->name
            ]);

        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    /** @test **/
    public function update_method_validates_required_fields()
    {
        $author = factory(\App\Author::class)->create();
        $this->put("/authors/{$author->id}", [], ['Accept' => 'application/json']);
        $this->seeStatusCode(422);
        $data = $this->response->getData(true);

        $fields = ['name', 'gender', 'biography'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    /** @test **/
    public function delete_can_remove_an_author_and_his_or_her_books()
    {
        $author = factory(\App\Author::class)->create();

        $this
            ->delete("/authors/{$author->id}")
            ->seeStatusCode(204)
            ->notSeeInDatabase('authors', ['id' => $author->id])
            ->notSeeInDatabase('books', ['author_id' => $author->id]);
    }

    /** @test **/
    public function deleting_an_invalid_author_should_return_a_404()
    {
        $this
            ->delete('/authors/99999', [], ['Accept' => 'application/json'])
            ->seeStatusCode(404);
    }

    /**
     * Provides boilerplate test instructions for validation.
     * @return array
     */
    private function getValidationTestData()
    {
        $author = factory(\App\Author::class)->create();
        return [
            // Create
            [
                'method' => 'post',
                'url' => '/authors',
                'status' => 201,
                'data' => [
                    'name' => 'John Doe',
                    'gender' => 'male',
                    'biography' => 'An anonymous author'
                ]
            ],

            // Update
            [
                'method' => 'put',
                'url' => "/authors/{$author->id}",
                'status' => 200,
                'data' => [
                    'name' => $author->name,
                    'gender' => $author->gender,
                    'biography' => $author->biography
                ]
            ]
        ];
    }
}

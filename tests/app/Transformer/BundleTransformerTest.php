<?php

namespace Tests\App\Transformer;

use TestCase;
use App\Transformer\BundleTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;

class BundleTransformerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var BundleTransformer
     */
    private $subject;

    public function setUp()
    {
        parent::setUp();

        $this->subject = new BundleTransformer();
    }

    /** @test **/
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(
            BundleTransformer::class,
            $this->subject
        );
    }

    /** @test **/
    public function it_can_transform_a_bundle()
    {
        $bundle = factory(\App\Bundle::class)->create();

        $actual = $this->subject->transform($bundle);

        $this->assertEquals($bundle->id, $actual['id']);
        $this->assertEquals($bundle->title, $actual['title']);
        $this->assertEquals(
            $bundle->description,
            $actual['description']
        );
        $this->assertEquals(
            $bundle->created_at->toIso8601String(),
            $actual['created']
        );
        $this->assertEquals(
            $bundle->updated_at->toIso8601String(),
            $actual['updated']
        );
    }

    /** @test **/
    public function it_can_transform_related_books()
    {
        $bundle = $this->bundleFactory();

        $data = $this->subject->includeBooks($bundle);
        $this->assertInstanceOf(
            \League\Fractal\Resource\Collection::class,
            $data
        );
        $this->assertInstanceOf(
            \App\Book::class,
            $data->getData()[0]
        );
        $this->assertCount(2, $data->getData());
    }
 }

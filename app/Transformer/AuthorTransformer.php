<?php

namespace App\Transformer;

use App\Author;
use League\Fractal\TransformerAbstract;

class AuthorTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'books'
    ];

    public function includeBooks(Author $author)
    {
        return $this->collection($author->books, new BookTransformer());
    }
    
    /**
     * Transform an author model
     *
     * @param Author $author
     * @return array
     */
    public function transform(Author $author)
    {
        return [
            'id'        => $author->id,
            'name'      => $author->name,
            'gender'    => $author->gender,
            'biography' => $author->biography,
            'rating' => [
                'average' => (float) sprintf(
                    "%.2f",
                    $author->ratings->avg('value')
                ),
                'max' => (float) sprintf("%.2f", 5),
                'percent' => (float) sprintf(
                    "%.2f",
                    ($author->ratings->avg('value') / 5) * 100
                ),
                'count' => $author->ratings->count(),
            ],
            'created'   => $author->created_at->toIso8601String(),
            'updated'   => $author->created_at->toIso8601String(),
        ];
    }
}

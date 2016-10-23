<?php

namespace App\Transformer;

use App\Rating;
use League\Fractal\TransformerAbstract;

/**
 * Class RatingTransformer
 * @package App\Transformer
 */
class RatingTransformer extends TransformerAbstract
{
    /**
     * Transform a Rating
     *
     * @param Rating $rating
     * @return array
     */
    public function transform(Rating $rating)
    {
        return [
            'id' => $rating->id,
            'value' => $rating->value,
            'type' => $rating->rateable_type,
            'links' => [
                [
                    'rel' => $this->getModelName($rating->rateable_type),
                    'href' => $this->getModelUrl($rating)
                ]
            ],
            'created' => $rating->created_at->toIso8601String(),
            'updated' => $rating->updated_at->toIso8601String(),
        ];
    }

    /**
     * Get a human-friendly model name
     *
     * @param $rateable_type
     * @return string
     */
    private function getModelName($rateable_type)
    {
        return strtolower(preg_replace("/^App\\\/", '', $rateable_type));
    }

    /**
     * Generate a URL to the rated model resource
     *
     * @param Rating $rating
     * @return string
     */
    private function getModelUrl(Rating $rating)
    {
        $author = \App\Author::class;
        $book = \App\Author::class;

        switch ($rating->rateable_type) {
            case $author:
                $named = 'authors.show';
                break;
            case $book:
                $named = 'books.show';
                break;
            default:
                throw new \RuntimeException(sprintf(
                    'Rateable model type for %s is not defined',
                    $rating->rateable_type
                ));
        }

        return route($named, ['id' => $rating->rateable_id]);
    }
}

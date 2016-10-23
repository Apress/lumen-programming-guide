<?php

namespace App\Transformer;

use App\Bundle;
use League\Fractal\TransformerAbstract;

/**
 * Class BundleTransformer
 * @package App\Transformer
 */
class BundleTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['books'];

    /**
     * Include a bundle's books
     * @param Bundle $bundle
     * @return \League\Fractal\Resource\Collection
     */
    public function includeBooks(Bundle $bundle)
    {
        return $this->collection($bundle->books, new BookTransformer());
    }

    /**
     * Transform a bundle
     *
     * @param Bundle $bundle
     * @return array
     */
    public function transform(Bundle $bundle)
    {
        return [
            'id' => $bundle->id,
            'title' => $bundle->title,
            'description' => $bundle->description,
            'created' => $bundle->created_at->toIso8601String(),
            'updated' => $bundle->updated_at->toIso8601String(),
        ];
    }
}

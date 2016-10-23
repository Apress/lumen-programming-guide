<?php

namespace App\Http\Controllers;

use App\Bundle;
use App\Transformer\BundleTransformer;

/**
 * Class BundlesController
 * @package App\Http\Controllers
 */
class BundlesController extends Controller
{
    public function show($id)
    {
        $bundle = Bundle::findOrFail($id);
        $data = $this->item($bundle, new BundleTransformer());

        return response()->json($data);
    }

    /**
     * @param int $bundleId
     * @param int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBook($bundleId, $bookId)
    {
        $bundle = \App\Bundle::findOrFail($bundleId);
        $book = \App\Book::findOrFail($bookId);

        $bundle->books()->attach($book);
        $data = $this->item($bundle, new BundleTransformer());

        return response()->json($data);
    }

    public function removeBook($bundleId, $bookId)
    {
        $bundle = \App\Bundle::findOrFail($bundleId);
        $book = \App\Book::findOrFail($bookId);

        $bundle->books()->detach($book);

        return response(null, 204);
    }
}

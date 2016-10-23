<?php

namespace App\Providers;

use League\Fractal\Manager;
use App\Http\Response\FractalResponse;
use Illuminate\Support\ServiceProvider;
use League\Fractal\Serializer\DataArraySerializer;

class FractalServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the DataArraySerializer to an interface contract
        $this->app->bind(
            'League\Fractal\Serializer\SerializerAbstract',
            'League\Fractal\Serializer\DataArraySerializer'
        );

        $this->app->bind(FractalResponse::class, function ($app) {
        $manager = new Manager();
        $serializer = $app['League\Fractal\Serializer\SerializerAbstract'];

            return new FractalResponse($manager, $serializer, $app['request']);
        });

        $this->app->alias(FractalResponse::class, 'fractal');
    }
}

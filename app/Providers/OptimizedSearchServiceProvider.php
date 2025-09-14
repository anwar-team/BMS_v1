<?php

namespace App\Providers;

use App\Scout\Engines\OptimizedElasticsearchEngine;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class OptimizedSearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        resolve(EngineManager::class)->extend('optimized_elastic', function () {
            // إنشاء Elasticsearch client محسن
            $client = ClientBuilder::create()
                ->setHosts([config('services.elasticsearch.host')])
                ->setConnectionPool('\Elasticsearch\ConnectionPool\StaticNoPingConnectionPool')
                ->setSelector('\Elasticsearch\ConnectionPool\Selectors\RoundRobinSelector')
                ->setRetries(2)
                ->setSSLVerification(false)
                ->build();

            return new OptimizedElasticsearchEngine($client);
        });
    }
}
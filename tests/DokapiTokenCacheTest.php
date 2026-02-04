<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use PHPUnit\Framework\Attributes\Test;

class DokapiTokenCacheTest extends DokapiTestCase
{
    #[Test]
    public function it_caches_access_tokens_between_requests()
    {
        $config = [
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'token_url' => 'https://dev-portal.dokapi.io/api/oauth2/token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'cache_token' => true,
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'token-123', 'expires_in' => 3600])),
            new Response(200, [], json_encode([])),
            new Response(200, [], json_encode([])),
        ]);

        $historyContainer = [];
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($historyContainer));
        $client = new Client(['handler' => $handlerStack]);
        $cache = new Repository(new ArrayStore());

        $dokapi = new DokapiClient($config, $client, $cache);

        $dokapi->listWebhooks();
        $dokapi->listWebhooks();

        $tokenRequests = array_filter(
            $historyContainer,
            fn(array $entry) => $entry['request']->getUri()->getPath() === '/api/oauth2/token'
        );

        $this->assertCount(1, $tokenRequests);
        $this->assertCount(3, $historyContainer);
    }
}

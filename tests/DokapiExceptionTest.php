<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Exceptions\DokapiAuthException;
use AndyFraussen\Dokapi\Exceptions\DokapiClientException;
use AndyFraussen\Dokapi\Exceptions\DokapiNotFoundException;
use AndyFraussen\Dokapi\Exceptions\DokapiRateLimitException;
use AndyFraussen\Dokapi\Exceptions\DokapiServerException;
use AndyFraussen\Dokapi\Exceptions\DokapiValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class DokapiExceptionTest extends DokapiTestCase
{
    #[Test]
    public function it_throws_auth_exception_on_401()
    {
        $client = $this->clientWithMockResponse(401, 'unauthorized');

        $this->expectException(DokapiAuthException::class);
        $client->listWebhooks();
    }

    #[Test]
    public function it_throws_not_found_exception_on_404()
    {
        $client = $this->clientWithMockResponse(404, 'not found');

        $this->expectException(DokapiNotFoundException::class);
        $client->listWebhooks();
    }

    #[Test]
    public function it_throws_validation_exception_on_422()
    {
        $client = $this->clientWithMockResponse(422, 'invalid');

        $this->expectException(DokapiValidationException::class);
        $client->listWebhooks();
    }

    #[Test]
    public function it_throws_rate_limit_exception_on_429()
    {
        $client = $this->clientWithMockResponse(429, 'rate limit');

        $this->expectException(DokapiRateLimitException::class);
        $client->listWebhooks();
    }

    #[Test]
    public function it_throws_server_exception_on_500()
    {
        $client = $this->clientWithMockResponse(500, 'server error');

        $this->expectException(DokapiServerException::class);
        $client->listWebhooks();
    }

    #[Test]
    public function it_throws_client_exception_on_unhandled_4xx()
    {
        $client = $this->clientWithMockResponse(409, 'conflict');

        $this->expectException(DokapiClientException::class);
        $client->listWebhooks();
    }

    private function clientWithMockResponse(int $status, string $body): DokapiClient
    {
        $config = [
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'access_token' => 'token-123',
        ];

        $mock = new MockHandler([
            new Response($status, [], $body),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $http = new Client(['handler' => $handlerStack]);

        return new DokapiClient($config, $http);
    }
}

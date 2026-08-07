<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Exceptions\DokapiException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DokapiTokenCacheTest extends DokapiTestCase
{
    /** @var array<int, array{request: \Psr\Http\Message\RequestInterface}> */
    private array $history = [];

    #[Test]
    public function it_caches_access_tokens_between_requests()
    {
        $dokapi = $this->clientWithResponses([
            new Response(200, [], json_encode(['access_token' => 'token-123', 'expires_in' => 3600])),
            new Response(200, [], json_encode([])),
            new Response(200, [], json_encode([])),
        ]);

        $dokapi->listWebhooks();
        $dokapi->listWebhooks();

        $this->assertSame(1, $this->countTokenRequests());
        $this->assertCount(3, $this->history);
    }

    #[Test]
    public function it_does_not_cache_a_token_that_expires_within_the_safety_margin(): void
    {
        $dokapi = $this->clientWithResponses([
            $this->tokenResponse('first-token', 60),
            new Response(200, [], '[]'),
            $this->tokenResponse('second-token', 60),
            new Response(200, [], '[]'),
        ]);

        $dokapi->listWebhooks();
        $dokapi->listWebhooks();

        $this->assertSame(2, $this->countTokenRequests());
    }

    #[DataProvider('uncacheableExpiryValues')]
    #[Test]
    public function it_does_not_cache_tokens_with_an_uncacheable_expiry(mixed $expiresIn): void
    {
        $dokapi = $this->clientWithResponses([
            $this->tokenResponse('first-token', $expiresIn),
            new Response(200, [], '[]'),
            $this->tokenResponse('second-token', $expiresIn),
            new Response(200, [], '[]'),
        ]);

        $dokapi->listWebhooks();
        $dokapi->listWebhooks();

        $this->assertSame(2, $this->countTokenRequests());
    }

    #[DataProvider('invalidAccessTokens')]
    #[Test]
    public function it_rejects_invalid_oauth_access_tokens(mixed $accessToken): void
    {
        $dokapi = $this->clientWithResponses([
            $this->tokenResponse($accessToken, 3600),
        ]);

        $this->expectException(DokapiException::class);

        $dokapi->listWebhooks();
    }

    public static function invalidAccessTokens(): iterable
    {
        yield 'blank string' => [''];
        yield 'integer' => [123];
        yield 'array' => [[]];
    }

    public static function uncacheableExpiryValues(): iterable
    {
        yield 'missing' => [null];
        yield 'non-numeric' => ['not-a-number'];
        yield 'non-positive' => [0];
    }

    /**
     * @param array<int, Response> $responses
     */
    private function clientWithResponses(array $responses): DokapiClient
    {
        $this->history = [];

        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new DokapiClient([
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'token_url' => 'https://dev-portal.dokapi.io/api/oauth2/token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'cache_token' => true,
        ], new Client(['handler' => $stack]), new Repository(new ArrayStore()));
    }

    private function tokenResponse(mixed $accessToken, mixed $expiresIn): Response
    {
        $payload = ['access_token' => $accessToken];
        if ($expiresIn !== null) {
            $payload['expires_in'] = $expiresIn;
        }

        return new Response(200, [], json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function countTokenRequests(): int
    {
        return count(array_filter(
            $this->history,
            static fn(array $entry): bool => $entry['request']->getUri()->getPath() === '/api/oauth2/token'
        ));
    }
}

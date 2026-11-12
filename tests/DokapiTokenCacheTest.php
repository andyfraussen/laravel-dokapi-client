<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Exceptions\DokapiException;
use AndyFraussen\Dokapi\Exceptions\DokapiAuthException;
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
    public function a_401_evicts_the_cached_token_without_replaying_the_request(): void
    {
        $dokapi = $this->clientWithResponses([
            $this->tokenResponse('revoked-token', 3600),
            new Response(200, [], '[]'),
            new Response(401, [], 'Revoked'),
            $this->tokenResponse('replacement-token', 3600),
            new Response(200, [], '[]'),
        ]);
        $dokapi->listWebhooks();

        try {
            $dokapi->createOutgoingDocument([]);
            $this->fail('Expected an authentication exception.');
        } catch (DokapiAuthException) {
            $this->assertCount(3, $this->history);
            $this->assertSame(1, $this->countTokenRequests());
        }

        $dokapi->listWebhooks();
        $this->assertSame(2, $this->countTokenRequests());
        $this->assertSame('Bearer replacement-token', $this->history[4]['request']->getHeaderLine('Authorization'));
        $posts = array_filter($this->history, static fn(array $entry) => $entry['request']->getUri()->getPath() === '/v1/outgoing-peppol-documents');
        $this->assertCount(1, $posts);
    }

    #[Test]
    public function a_403_does_not_discard_a_valid_token_for_a_permission_error(): void
    {
        $dokapi = $this->clientWithResponses([
            $this->tokenResponse('valid-token', 3600),
            new Response(403, [], 'Forbidden'),
            new Response(200, [], '[]'),
        ]);
        try {
            $dokapi->listWebhooks();
            $this->fail('Expected an authentication exception.');
        } catch (DokapiAuthException) {
        }
        $dokapi->listWebhooks();
        $this->assertSame(1, $this->countTokenRequests());
    }

    #[Test]
    public function callers_can_explicitly_forget_and_refresh_oauth_tokens(): void
    {
        $dokapi = $this->clientWithResponses([
            $this->tokenResponse('first-token', 3600),
            new Response(200, [], '[]'),
            $this->tokenResponse('second-token', 3600),
            $this->tokenResponse('third-token', 3600),
            new Response(200, [], '[]'),
        ]);
        $dokapi->listWebhooks();
        $this->assertSame('second-token', $dokapi->refreshAccessToken());
        $dokapi->forgetAccessToken();
        $this->assertCount(3, $this->history);
        $dokapi->listWebhooks();
        $this->assertSame(3, $this->countTokenRequests());
        $this->assertSame('Bearer third-token', $this->history[4]['request']->getHeaderLine('Authorization'));
    }

    #[Test]
    public function static_access_tokens_are_not_refreshed_or_used_to_request_oauth_tokens(): void
    {
        $dokapi = $this->clientWithResponses([new Response(401, [], 'Invalid static token')], ['access_token' => 'static-token']);
        $this->assertSame('static-token', $dokapi->refreshAccessToken());
        try {
            $dokapi->listWebhooks();
            $this->fail('Expected an authentication exception.');
        } catch (DokapiAuthException) {
        }
        $this->assertSame(0, $this->countTokenRequests());
        $this->assertSame('static-token', $dokapi->refreshAccessToken());
    }

    #[Test]
    public function token_invalidation_is_scoped_to_the_api_environment(): void
    {
        $dokapi = $this->clientWithResponses([
            $this->tokenResponse('peppol-token', 3600), new Response(200, [], '[]'),
            $this->tokenResponse('mandate-token', 3600), new Response(200, [], '{}'),
            new Response(401, [], 'Revoked'), new Response(200, [], '{}'),
        ], ['mandate_base_url' => 'https://mandate.example.test/v1']);
        $dokapi->listWebhooks();
        $dokapi->getMandate('first');
        try {
            $dokapi->listWebhooks();
            $this->fail('Expected an authentication exception.');
        } catch (DokapiAuthException) {
        }
        $dokapi->getMandate('second');
        $this->assertSame(2, $this->countTokenRequests());
        $this->assertSame('Bearer mandate-token', $this->history[5]['request']->getHeaderLine('Authorization'));
    }

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
    private function clientWithResponses(array $responses, array $overrides = []): DokapiClient
    {
        $this->history = [];

        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new DokapiClient(array_replace([
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'token_url' => 'https://dev-portal.dokapi.io/api/oauth2/token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'cache_token' => true,
        ], $overrides), new Client(['handler' => $stack]), new Repository(new ArrayStore()));
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

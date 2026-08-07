<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Exceptions\DokapiValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class DokapiHttpBoundaryTest extends DokapiTestCase
{
    /** @var array<int, array{request: \Psr\Http\Message\RequestInterface, response: \Psr\Http\Message\ResponseInterface|null, error: \Throwable|null, options: array}> */
    private array $history = [];

    #[Test]
    public function it_preserves_the_base_uri_path_for_relative_api_requests(): void
    {
        $client = $this->historyClient([new Response(200, [], 'ok')]);

        $this->client($client)->getStatus();

        $this->assertSame(
            'https://peppol-api.dokapi-stg.io/v1/status',
            (string) $this->history[0]['request']->getUri()
        );
    }

    #[Test]
    public function it_encodes_dynamic_webhook_path_segments(): void
    {
        $client = $this->historyClient([new Response(204)]);

        $this->client($client)->deleteWebhook('../clients/deactivate-client-registration?discard=1');

        $this->assertSame(
            '/v1/webhooks/..%2Fclients%2Fdeactivate-client-registration%3Fdiscard%3D1',
            $this->history[0]['request']->getUri()->getPath()
        );
    }

    #[Test]
    public function it_encodes_incoming_document_path_segments(): void
    {
        $client = $this->historyClient([new Response(200, [], 'ok')]);

        $this->client($client)->confirmIncomingDocument('../webhooks?discard=1');

        $this->assertSame(
            '/v1/incoming-peppol-documents/..%2Fwebhooks%3Fdiscard%3D1/confirm',
            $this->history[0]['request']->getUri()->getPath()
        );
    }

    #[Test]
    public function it_maps_4xx_responses_when_http_errors_is_enabled_in_config(): void
    {
        $client = $this->historyClient([new Response(422, [], '{"title":"Invalid"}')]);

        $this->expectException(DokapiValidationException::class);

        $this->client($client, ['http' => ['http_errors' => true]])->listWebhooks();
    }

    /**
     * @param array<int, Response> $responses
     */
    private function historyClient(array $responses): Client
    {
        $this->history = [];

        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new Client(['handler' => $stack]);
    }

    private function client(Client $http, array $overrides = []): DokapiClient
    {
        return new DokapiClient(array_replace([
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'access_token' => 'static-token',
        ], $overrides), $http);
    }
}

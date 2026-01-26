<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Requests\WebhookRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class DokapiWebhookTest extends DokapiTestCase
{
    #[Test]
    public function it_creates_a_webhook()
    {
        $config = [
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'token_url' => 'https://dev-portal.dokapi.io/api/oauth2/token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'cache_token' => false,
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'token-123', 'expires_in' => 3600])),
            new Response(201, [], json_encode([
                'message' => 'Webhook created successfully',
                'webhook' => [
                    'ulid' => '01WEB',
                    'clientUlid' => '01CLIENT',
                    'creationTimestamp' => '2024-03-08T15:34:18.242Z',
                    'lastModifiedTimestamp' => '2024-03-08T15:34:18.242Z',
                    'url' => 'https://example.com/webhook',
                    'events' => ['outgoing-peppol-documents.sent'],
                ],
            ])),
            new Response(200, [], json_encode(['access_token' => 'token-123', 'expires_in' => 3600])),
            new Response(201, [], json_encode([
                'message' => 'Webhook created successfully',
                'webhook' => [
                    'ulid' => '01WEB',
                    'clientUlid' => '01CLIENT',
                    'creationTimestamp' => '2024-03-08T15:34:18.242Z',
                    'lastModifiedTimestamp' => '2024-03-08T15:34:18.242Z',
                    'url' => 'https://example.com/webhook',
                    'events' => ['outgoing-peppol-documents.sent'],
                ],
            ])),
        ]);

        $historyContainer = [];
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($historyContainer));
        $client = new Client(['handler' => $handlerStack]);

        $dokapi = new DokapiClient($config, $client);

        $payload = new WebhookRequest('https://example.com/webhook', ['outgoing-peppol-documents.sent']);
        $response = $dokapi->api()->webhooks->create($payload);

        $this->assertSame('Webhook created successfully', $response['message']);
        $this->assertCount(2, $historyContainer);
        $this->assertSame('POST', $historyContainer[1]['request']->getMethod());
        $this->assertSame('/webhooks', $historyContainer[1]['request']->getUri()->getPath());

        $dto = $dokapi->api()->webhooks->createDto($payload);
        $this->assertSame('https://example.com/webhook', $dto->webhook->url);
        $this->assertCount(4, $historyContainer);
    }
}

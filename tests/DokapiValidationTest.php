<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Requests\ValidatingDocumentRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class DokapiValidationTest extends DokapiTestCase
{
    #[Test]
    public function it_creates_and_uploads_a_validating_document()
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
                'message' => 'ValidatingPeppolDocument created successfully',
                'document' => ['ulid' => '01VAL'],
                'preSignedUploadUrl' => 'https://upload.example.com/put',
            ])),
            new Response(200, [], ''),
        ]);

        $historyContainer = [];
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($historyContainer));
        $client = new Client(['handler' => $handlerStack]);

        $dokapi = new DokapiClient($config, $client);

        $payload = new ValidatingDocumentRequest('ref-123');
        $response = $dokapi->api()->validations->sendDto('<root/>', $payload);

        $this->assertSame('https://upload.example.com/put', $response->preSignedUploadUrl);
        $this->assertCount(3, $historyContainer);
        $this->assertSame('/validating-peppol-documents', $historyContainer[1]['request']->getUri()->getPath());
        $this->assertSame('PUT', $historyContainer[2]['request']->getMethod());
    }
}

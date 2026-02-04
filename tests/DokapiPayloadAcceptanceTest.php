<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Dto\DocumentTypeIdentifier;
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Dto\ProcessIdentifier;
use AndyFraussen\Dokapi\Requests\OutgoingDocumentRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class DokapiPayloadAcceptanceTest extends DokapiTestCase
{
    #[Test]
    public function it_accepts_payload_interface_on_client_methods()
    {
        $config = [
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'token_url' => 'https://dev-portal.dokapi.io/api/oauth2/token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'cache_token' => false,
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'token-123',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ])),
            new Response(201, [], json_encode([
                'message' => 'OutgoingPeppolDocument created successfully',
                'document' => ['ulid' => '01ABC'],
                'preSignedUploadUrl' => 'https://upload.example.com/put',
            ])),
            new Response(200, [], ''),
        ]);

        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $dokapi = new DokapiClient($config, $client);

        $payload = new OutgoingDocumentRequest(
            ParticipantIdentifier::of('0208:0123456789'),
            ParticipantIdentifier::of('0208:9876543210'),
            'BE',
            DocumentTypeIdentifier::of('doctype'),
            ProcessIdentifier::of('process'),
            'ref-1'
        );

        $response = $dokapi->sendOutgoingDocument($payload, '<root/>');

        $this->assertSame('https://upload.example.com/put', $response['preSignedUploadUrl']);
    }
}

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
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

use PHPUnit\Framework\Attributes\Test;

class DokapiClientTest extends DokapiTestCase
{
    #[Test]
    public function it_creates_and_uploads_an_outgoing_document()
    {
        $config = [
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'token_url' => 'https://dev-portal.dokapi.io/api/oauth2/token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'cache_token' => false,
            'timeout' => 10,
            'connect_timeout' => 5,
            'verify' => true,
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

        $historyContainer = [];
        $history = Middleware::history($historyContainer);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);
        $cache = new Repository(new ArrayStore());

        $dokapi = new DokapiClient($config, $client, $cache);

        $metadata = [
            'sender' => ['value' => '0208:0123456789'],
            'receiver' => ['value' => '0208:9876543210'],
            'c1CountryCode' => 'BE',
            'documentTypeIdentifier' => ['value' => 'doctype'],
            'processIdentifier' => ['value' => 'process'],
            'externalReference' => 'ref-1',
        ];

        $response = $dokapi->api()->outgoingDocuments->send($metadata, '<root/>');

        $this->assertSame('https://upload.example.com/put', $response['preSignedUploadUrl']);
        $this->assertCount(3, $historyContainer);
        $this->assertSame('POST', $historyContainer[0]['request']->getMethod());
        $this->assertSame('/api/oauth2/token', $historyContainer[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $historyContainer[1]['request']->getMethod());
        $this->assertSame('/outgoing-peppol-documents', $historyContainer[1]['request']->getUri()->getPath());
        $this->assertSame('PUT', $historyContainer[2]['request']->getMethod());

        $payload = new OutgoingDocumentRequest(
            ParticipantIdentifier::of('0208:0123456789'),
            ParticipantIdentifier::of('0208:9876543210'),
            'BE',
            DocumentTypeIdentifier::of('doctype'),
            ProcessIdentifier::of('process'),
            'ref-1'
        );

        $dto = $dokapi->api()->outgoingDocuments->sendDto($payload, '<root/>');
        $this->assertSame('https://upload.example.com/put', $dto->preSignedUploadUrl);
    }
}

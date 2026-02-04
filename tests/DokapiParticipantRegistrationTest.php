<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Dto\ProblemDetail;
use AndyFraussen\Dokapi\Requests\ParticipantRegistrationRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

class DokapiParticipantRegistrationTest extends DokapiTestCase
{
    #[Test]
    public function it_returns_problem_detail_for_partial_registration()
    {
        $config = [
            'base_url' => 'https://peppol-api.dokapi-stg.io/v1',
            'access_token' => 'token-123',
        ];

        $mock = new MockHandler([
            new Response(207, [], json_encode([
                'title' => 'Partial Success',
                'detail' => 'Business card failed',
                'status' => 207,
                'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            ])),
        ]);

        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $dokapi = new DokapiClient($config, $client);

        $payload = new ParticipantRegistrationRequest(
            ParticipantIdentifier::of('0208:0123456789'),
            'BE'
        );

        $result = $dokapi->api()->participants->registerDto($payload);

        $this->assertInstanceOf(ProblemDetail::class, $result);
        $this->assertSame('Partial Success', $result->title);
        $this->assertSame('Business card failed', $result->detail);
        $this->assertSame(207, $result->status);
    }
}

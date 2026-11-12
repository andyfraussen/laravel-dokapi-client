<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Dto\Mandates\KycData;
use AndyFraussen\Dokapi\Dto\Mandates\MandateCapability;
use AndyFraussen\Dokapi\Dto\Mandates\PeppolEntity;
use AndyFraussen\Dokapi\Enums\MandateCapabilityScope;
use AndyFraussen\Dokapi\Enums\MandateCapabilityStatus;
use AndyFraussen\Dokapi\Enums\MandateStatus;
use AndyFraussen\Dokapi\Exceptions\DokapiException;
use AndyFraussen\Dokapi\Exceptions\DokapiServerException;
use AndyFraussen\Dokapi\Exceptions\DokapiValidationException;
use AndyFraussen\Dokapi\Requests\BelgiumMandateRequest;
use AndyFraussen\Dokapi\Requests\WebhookRequest;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DokapiMandateTest extends ApiContractTestCase
{
    private function belgiumRequest(): BelgiumMandateRequest
    {
        $sample = $this->fixture('mandate-request');

        return new BelgiumMandateRequest(
            $sample['startDate'],
            $sample['identifier']['kbo'],
            KycData::fromArray($sample['kycData']),
            array_map(static fn(array $entity) => PeppolEntity::fromArray($entity), $sample['peppolEntities']),
        );
    }

    #[Test]
    #[DataProvider('creationResponses')]
    public function it_submits_the_documented_belgian_onboarding_request_and_preserves_the_contract_specific_response(string $fixture, MandateStatus $status, bool $needsUpload): void
    {
        $body = $this->fixtureJson($fixture);
        $this->assertMatchesSchema(json_decode($body), 'mandate', 'RequestMandateResponseDto');
        $client = $this->mockClient([new Response(201, [], $body)], ['mandate_base_url' => 'https://mandate.example.test/gateway/v1/']);
        $response = $client->api()->mandates->requestBelgiumDto($this->belgiumRequest());

        $this->assertSame('https://mandate.example.test/gateway/v1/mandates/be', (string) $this->history[0]['request']->getUri());
        $this->assertSame('Bearer test-token', $this->history[0]['request']->getHeaderLine('Authorization'));
        $this->assertMatchesSchema($this->requestJson(), 'mandate', 'BelgiumParticipantMandateRequestDto');
        $this->assertEquals(json_decode($this->fixtureJson('mandate-request')), $this->requestJson());
        $this->assertSame($status, $response->mandate->status);
        $this->assertSame('0123456789', $response->mandate->identifier['kbo']);
        $this->assertSame($needsUpload, $response->additionalDocumentUpload !== null);
        $this->assertSame('Example Company BV', $response->mandate->kycData->company->name);
        if ($needsUpload) {
            $this->assertNotEmpty($response->additionalDocumentUpload->key);
            $this->assertStringStartsWith('https://', $response->additionalDocumentUpload->url);
        }
    }

    public static function creationResponses(): iterable
    {
        yield 'third party contract' => ['mandate-pending-upload', MandateStatus::PendingUpload, true];
        yield 'Dokapi contract' => ['mandate-requested', MandateStatus::Requested, false];
    }

    #[Test]
    public function it_supports_raw_payloads_and_encodes_mandate_identifiers(): void
    {
        $client = $this->mockClient([
            new Response(201, [], $this->fixtureJson('mandate-requested')),
            new Response(200, [], $this->fixtureJson('mandate-active')),
        ]);
        $response = $client->api()->mandates->requestBelgium($this->fixture('mandate-request'));
        $this->assertArrayHasKey('mandate', $response);
        $client->api()->mandates->get('../clients?discard=1');
        $this->assertSame('/v1/mandates/..%2Fclients%3Fdiscard%3D1', $this->history[1]['request']->getUri()->getPath());
    }

    #[Test]
    public function it_hydrates_capabilities_kyc_history_and_document_keys_from_a_mandate(): void
    {
        $body = $this->fixtureJson('mandate-active');
        $this->assertMatchesSchema(json_decode($body), 'mandate', 'MandateDto');
        $client = $this->mockClient([new Response(200, [], $body)]);
        $mandate = $client->api()->mandates->getDto('01MANDATE');

        $this->assertSame(MandateStatus::Reviewed, $mandate->status);
        $this->assertSame(MandateCapabilityStatus::Active, $mandate->capabilities[0]->status);
        $this->assertSame(MandateCapabilityScope::Receiving, $mandate->capabilities[0]->scope);
        $this->assertTrue($mandate->capabilities[0]->isActive());
        $this->assertSame('0208:0123456789', $mandate->capabilities[0]->participantIdentifier->value);
        $this->assertSame('Antwerp', $mandate->kycData->company->address->cityName);
        $this->assertSame('Jane', $mandate->kycData->representative->personalInformation->firstName);
        $this->assertTrue($mandate->kycData->participantAgreement->paid);
        $this->assertNotEmpty($mandate->kycData->additionalDocuments);
        $this->assertSame('integration@example-company.be', $mandate->kycDataHistory[0]->submittedBy);
        $this->assertArrayNotHasKey('additionalDocuments', $mandate->kycData->toArray());
    }

    #[Test]
    public function mandate_lists_use_their_lowercase_items_and_opaque_pagination_cursor(): void
    {
        $body = $this->fixtureJson('mandate-page');
        $this->assertMatchesSchema(json_decode($body), 'mandate', 'MandatePageDto');
        $client = $this->mockClient([new Response(200, [], $body), new Response(200, [], '{"items":[],"count":0}')]);
        $cursor = 'opaque+/cursor=';
        $page = $client->api()->mandates->listDto($cursor, 7);
        $this->assertCount(5, $page->items);
        $this->assertSame(['BE', 'NL', 'FR', 'DE', 'LU'], array_map(static fn($mandate) => $mandate->countryCode, $page->items));
        $this->assertNotEmpty($page->lastEvaluatedKey);
        parse_str($this->history[0]['request']->getUri()->getQuery(), $query);
        $this->assertSame(['lastEvaluatedKey' => $cursor, 'limit' => '7'], $query);
        $this->assertSame(['items' => [], 'count' => 0], $client->api()->mandates->list());
        $this->assertSame('', $this->history[1]['request']->getUri()->getQuery());
    }

    #[Test]
    #[DataProvider('invalidMandateUrls')]
    public function mandate_calls_require_an_explicit_https_api_url(mixed $baseUrl): void
    {
        $client = $this->mockClient([], ['mandate_base_url' => $baseUrl]);
        try {
            $client->listMandates();
            $this->fail('Expected a configuration exception.');
        } catch (DokapiException $exception) {
            $this->assertStringContainsString('mandate_base_url', $exception->getMessage());
        }
        $this->assertCount(0, $this->history);
    }

    public static function invalidMandateUrls(): iterable
    {
        yield [null];
        yield [''];
        yield ['/v1'];
        yield ['http://mandate.example.test/v1'];
        yield ['https://user@mandate.example.test/v1'];
        yield ['https://mandate.example.test/v1?query=1'];
        yield ['https://mandate.example.test/v1#fragment'];
    }

    #[Test]
    public function peppol_requests_work_without_enabling_mandate_support(): void
    {
        $client = $this->mockClient([new Response(200, [], 'ok')], ['mandate_base_url' => null]);
        $this->assertSame('ok', $client->getStatus());
    }

    #[Test]
    public function mandate_webhook_management_uses_the_mandate_api(): void
    {
        $client = $this->mockClient([new Response(201, [], '{"message":"Created","webhook":{"ulid":"01HOOK"}}'), new Response(201, [], 'secret')]);
        $webhooks = $client->api()->mandates->webhooks();
        $webhooks->createDto(new WebhookRequest('https://example.test/hook', ['mandate.capability.active']));
        $this->assertSame('secret', $webhooks->secret());
        $this->assertSame('https://mandate.example.test/v1/webhooks', (string) $this->history[0]['request']->getUri());
        $this->assertSame('https://mandate.example.test/v1/webhooks/secretKey', (string) $this->history[1]['request']->getUri());
    }

    #[Test]
    public function kyc_uploads_use_raw_file_bytes_and_do_not_inherit_api_credentials_or_options(): void
    {
        $client = $this->mockClient([
            new Response(201, [], $this->fixtureJson('mandate-pending-upload')), new Response(200),
        ], ['http' => ['headers' => ['X-Private' => 'secret']]], [
            'headers' => ['Authorization' => 'Bearer injected', 'Cookie' => 'session=secret'],
            'query' => ['private' => 'secret'],
        ]);
        $response = $client->api()->mandates->requestBelgiumDto($this->belgiumRequest());
        $client->api()->mandates->uploadAdditionalDocument($response->additionalDocumentUpload, '%PDF-proof');
        $request = $this->history[1]['request'];
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame($response->additionalDocumentUpload->url, (string) $request->getUri());
        $this->assertSame('%PDF-proof', (string) $request->getBody());
        $this->assertSame('application/pdf', $request->getHeaderLine('Content-Type'));
        foreach (['Authorization', 'Cookie', 'X-Private'] as $header) {
            $this->assertFalse($request->hasHeader($header));
        }
        $this->assertFalse($this->history[1]['options']['allow_redirects']);
    }

    #[Test]
    public function kyc_uploads_accept_a_different_content_type_and_do_not_follow_redirects(): void
    {
        $client = $this->mockClient([new Response(307, ['Location' => 'https://other.example.test/file'])]);
        try {
            $client->api()->mandates->uploadAdditionalDocument('https://upload.example.test/proof', 'image-bytes', 'image/png');
            $this->fail('Expected an upload exception.');
        } catch (DokapiException) {
        }
        $this->assertCount(1, $this->history);
        $this->assertSame('image/png', $this->history[0]['request']->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function mandate_errors_preserve_typed_problem_details(): void
    {
        $client = $this->mockClient([new Response(400, [], '{"title":"Invalid mandate","detail":"KYC incomplete","status":400}')]);
        try {
            $client->requestBelgiumMandate([]);
            $this->fail('Expected a validation exception.');
        } catch (DokapiValidationException $exception) {
            $this->assertSame('KYC incomplete', $exception->getProblemDetail()->detail);
        }
    }

    #[Test]
    public function kyc_upload_server_errors_remain_visible_to_the_caller(): void
    {
        $client = $this->mockClient([new Response(503, [], 'Unavailable')]);
        $this->expectException(DokapiServerException::class);
        $client->uploadMandateDocument('https://upload.example.test/proof', 'proof');
    }

    #[Test]
    #[DataProvider('invalidDates')]
    public function mandate_requests_reject_invalid_dates_and_date_ranges(string $startDate, ?string $endDate): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BelgiumMandateRequest($startDate, '0123456789', $this->belgiumRequest()->kycData, endDate: $endDate);
    }

    public static function invalidDates(): iterable
    {
        yield ['not-a-date', null];
        yield ['2026-02-30', null];
        yield ['2026-1-01', null];
        yield ['2026-11-12', '2026-11-12'];
        yield ['2026-11-12', '2026-11-11'];
        yield ['2026-11-12', 'invalid'];
    }

    #[Test]
    public function mandate_requests_preserve_explicit_false_flags_and_omit_optional_entities(): void
    {
        $request = $this->belgiumRequest();
        $entity = new PeppolEntity($request->peppolEntities[0]->participantIdentifier, false, true);
        $payload = new BelgiumMandateRequest('2026-11-12', '0123456789', $request->kycData, [$entity], '2027-11-12');
        $this->assertMatchesSchema(json_decode(json_encode($payload->toArray())), 'mandate', 'BelgiumParticipantMandateRequestDto');
        $this->assertFalse($payload->toArray()['peppolEntities'][0]['sendingCapabilities']);
        $this->assertArrayNotHasKey('peppolEntities', (new BelgiumMandateRequest('2026-11-12', '0123456789', $request->kycData))->toArray());
        $this->expectException(InvalidArgumentException::class);
        new BelgiumMandateRequest('2026-11-12', '0123456789', $request->kycData, array_fill(0, 11, $entity));
    }

    #[Test]
    public function unknown_future_capability_values_are_preserved_without_becoming_active(): void
    {
        $body = $this->fixture('mandate-capability-feedback')['body'];
        $capability = MandateCapability::fromArray(array_replace($body, ['status' => 'FUTURE_STATUS', 'scope' => 'future-scope']));
        $this->assertSame('FUTURE_STATUS', $capability->status);
        $this->assertSame('future-scope', $capability->scope);
        $this->assertFalse($capability->isActive());
    }
}

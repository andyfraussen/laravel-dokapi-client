<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Dto\Mandates\MandateCapability;
use AndyFraussen\Dokapi\Dto\Mandates\MandateStatusInformation;
use AndyFraussen\Dokapi\Enums\MandateCapabilityStatus;
use AndyFraussen\Dokapi\Enums\MandateEvent;
use AndyFraussen\Dokapi\Enums\MandateStatus;
use AndyFraussen\Dokapi\Exceptions\DokapiException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DokapiMandateWebhookTest extends ApiContractTestCase
{
    #[Test]
    public function it_authenticates_and_parses_capability_feedback_with_separate_invocation_and_entity_ids(): void
    {
        $payload = $this->fixtureJson('mandate-capability-feedback');
        $this->assertMatchesSchema(json_decode($payload), 'mandate', 'MandateCapabilityStatusFeedbackDto');
        $webhooks = $this->mockClient([])->api()->webhooks;
        $feedback = $webhooks->parseMandateFeedback($payload, hash_hmac('sha256', $payload, 'secret'), 'secret');
        $this->assertSame(MandateEvent::CapabilityActive, $feedback->event);
        $this->assertInstanceOf(MandateCapability::class, $feedback->body);
        $this->assertSame(MandateCapabilityStatus::Active, $feedback->body->status);
        $this->assertNotSame($feedback->ulid, $feedback->body->ulid);
        $this->assertNotEmpty($feedback->body->mandateUlid);
        $this->assertNotEmpty($feedback->body->clientUlid);
        $this->assertTrue($feedback->body->isActive());
        $this->assertCount(0, $this->history);
    }

    #[Test]
    public function it_also_parses_mandate_status_feedback(): void
    {
        $payload = $this->fixtureJson('mandate-status-feedback');
        $this->assertMatchesSchema(json_decode($payload), 'mandate', 'MandateStatusFeedbackDto');
        $feedback = $this->mockClient([])->api()->webhooks->parseMandateFeedback($payload, hash_hmac('sha256', $payload, 'secret'), 'secret');
        $this->assertSame(MandateEvent::Reviewed, $feedback->event);
        $this->assertInstanceOf(MandateStatusInformation::class, $feedback->body);
        $this->assertSame(MandateStatus::Reviewed, $feedback->body->status);
        $this->assertSame('0123456789', $feedback->body->identifier['kbo']);
    }

    #[Test]
    #[DataProvider('invalidSignatures')]
    public function mandate_feedback_rejects_invalid_signatures_and_empty_secrets(string $signature, string $secret): void
    {
        $payload = $this->fixtureJson('mandate-capability-feedback');
        $this->expectException(DokapiException::class);
        $this->mockClient([])->api()->webhooks->parseMandateFeedback($payload, $signature, $secret);
    }

    public static function invalidSignatures(): iterable
    {
        yield 'missing signature' => ['', 'secret'];
        yield 'incorrect signature' => [str_repeat('a', 64), 'secret'];
        yield 'empty secret' => [str_repeat('a', 64), ''];
    }

    #[Test]
    public function payload_bytes_are_not_reencoded_before_hmac_verification(): void
    {
        $payload = $this->fixtureJson('mandate-status-feedback');
        $webhooks = $this->mockClient([])->api()->webhooks;
        $this->assertFalse($webhooks->verifySignature($payload, hash_hmac('sha256', $payload, ''), ''));
        $this->expectException(DokapiException::class);
        $webhooks->parseMandateFeedback($payload . "\n", hash_hmac('sha256', $payload, 'secret'), 'secret');
    }

    #[Test]
    #[DataProvider('invalidPayloads')]
    public function authenticated_malformed_payloads_raise_a_domain_exception(string $payload): void
    {
        $this->expectException(DokapiException::class);
        $this->mockClient([])->api()->webhooks->parseMandateFeedback($payload, hash_hmac('sha256', $payload, 'secret'), 'secret');
    }

    public static function invalidPayloads(): iterable
    {
        yield 'invalid JSON' => ['{'];
        yield 'scalar JSON' => ['"text"'];
        yield 'missing fields' => ['{}'];
        yield 'wrong event family' => ['{"event":"outgoing-peppol-documents.sent","ulid":"event","body":{}}'];
        yield 'missing capability fields' => ['{"event":"mandate.capability.active","ulid":"event","body":{"ulid":"capability"}}'];
        yield 'wrong scalar type' => ['{"event":"mandate.reviewed","ulid":"event","body":{"ulid":"mandate","clientUlid":"client","status":[],"statusMessage":"","identifier":{}}}'];
    }

    #[Test]
    #[DataProvider('invalidNestedBodies')]
    public function nested_malformed_feedback_is_rejected_without_type_errors_or_lossy_casts(string $fixture, array $overrides): void
    {
        $data = $this->fixture($fixture);
        $data['body'] = array_replace($data['body'], $overrides);
        $payload = json_encode($data);
        $this->expectException(DokapiException::class);
        $this->mockClient([])->api()->webhooks->parseMandateFeedback($payload, hash_hmac('sha256', $payload, 'secret'), 'secret');
    }

    public static function invalidNestedBodies(): iterable
    {
        yield 'array date' => ['mandate-capability-feedback', ['endDate' => []]];
        yield 'array scheme' => ['mandate-capability-feedback', ['participantIdentifier' => ['value' => '0208:0123456789', 'scheme' => []]]];
        yield 'nested identifier' => ['mandate-status-feedback', ['identifier' => ['kbo' => []]]];
        yield 'string entities' => ['mandate-status-feedback', ['peppolEntities' => 'invalid']];
        yield 'string entity' => ['mandate-status-feedback', ['peppolEntities' => ['invalid']]];
        yield 'string boolean' => ['mandate-status-feedback', ['peppolEntities' => [['participantIdentifier' => ['value' => '0208:0123456789'], 'receivingCapabilities' => 'false']]]];
    }

    #[Test]
    public function new_future_mandate_event_names_are_preserved(): void
    {
        $data = $this->fixture('mandate-status-feedback');
        $data['event'] = 'mandate.future-state';
        $payload = json_encode($data);
        $feedback = $this->mockClient([])->api()->webhooks->parseMandateFeedback($payload, hash_hmac('sha256', $payload, 'secret'), 'secret');
        $this->assertSame('mandate.future-state', $feedback->event);
    }
}

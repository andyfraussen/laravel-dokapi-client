<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Dto\BusinessCards\BusinessContact;
use AndyFraussen\Dokapi\Dto\BusinessCards\BusinessEntity;
use AndyFraussen\Dokapi\Dto\BusinessCards\BusinessIdentifier;
use AndyFraussen\Dokapi\Dto\BusinessCards\CompleteBusinessCard;
use AndyFraussen\Dokapi\Dto\BusinessCards\MultilingualName;
use AndyFraussen\Dokapi\Dto\DocumentTypeIdentifier;
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Dto\ProcessIdentifier;
use AndyFraussen\Dokapi\Requests\BusinessCardUpdateRequest;
use AndyFraussen\Dokapi\Requests\DocumentTypeDeregisterRequest;
use AndyFraussen\Dokapi\Requests\DocumentTypeRegisterRequest;
use AndyFraussen\Dokapi\Requests\OutgoingDocumentRequest;
use AndyFraussen\Dokapi\Requests\ParticipantRegistrationRequest;
use AndyFraussen\Dokapi\Requests\PayloadInterface;
use AndyFraussen\Dokapi\Requests\ValidatingDocumentRequest;
use AndyFraussen\Dokapi\Requests\WebhookRequest;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

class DokapiContractTest extends ApiContractTestCase
{
    #[Test]
    public function empty_validation_metadata_is_a_json_object_on_both_api_surfaces(): void
    {
        $client = $this->mockClient([new Response(201, [], '{}'), new Response(201, [], '{}')]);
        $client->createValidatingDocument();
        $client->api()->validations->create(new ValidatingDocumentRequest());

        foreach ([0, 1] as $index) {
            $this->assertInstanceOf(stdClass::class, $this->requestJson($index));
            $this->assertSame('{}', (string) $this->history[$index]['request']->getBody());
            $this->assertMatchesSchema($this->requestJson($index), 'peppol', 'ValidatingPeppolDocumentRequestDto');
        }
    }

    #[Test]
    #[DataProvider('requestContracts')]
    public function typed_requests_match_the_published_schema(string $method, PayloadInterface $payload, string $schema): void
    {
        $client = $this->mockClient([new Response(200, [], '{}')]);
        $client->$method($payload);

        $this->assertMatchesSchema($this->requestJson(), 'peppol', $schema);
    }

    public static function requestContracts(): iterable
    {
        $participant = ParticipantIdentifier::of('0208:0123456789');
        $documentType = DocumentTypeIdentifier::of('document-type', 'busdox-docid-qns');
        $process = ProcessIdentifier::of('process', 'cenbii-procid-ubl');
        $card = new CompleteBusinessCard([
            new BusinessEntity(
                name: [new MultilingualName('Example Company', 'nl')],
                countryCode: 'BE',
                identifier: [new BusinessIdentifier('0123456789', 'KBO')],
                websiteUri: ['https://example.test'],
                contact: [new BusinessContact(email: 'info@example.test'), new BusinessContact()],
            ),
        ]);

        yield 'outgoing' => ['createOutgoingDocument', new OutgoingDocumentRequest($participant, $participant, 'BE', $documentType, $process), 'OutgoingPeppolDocumentRequestDto'];
        yield 'validation with reference' => ['createValidatingDocument', new ValidatingDocumentRequest('invoice-123'), 'ValidatingPeppolDocumentRequestDto'];
        yield 'registration' => ['registerParticipant', new ParticipantRegistrationRequest($participant, 'BE', completeBusinessCard: $card), 'ParticipantRegistrationRequestDto'];
        yield 'business card' => ['updateBusinessCard', new BusinessCardUpdateRequest($participant, completeBusinessCard: $card), 'BusinessCardUpdateRequestDto'];
        yield 'register document type' => ['registerDocumentType', new DocumentTypeRegisterRequest($participant, $documentType, $process), 'DocumentTypeRegisterRequestDto'];
        yield 'deregister document type' => ['deregisterDocumentType', new DocumentTypeDeregisterRequest($participant, $documentType), 'DocumentTypeDeregisterRequestDto'];
        yield 'webhook' => ['createWebhook', new WebhookRequest('https://example.test/webhook', ['outgoing-peppol-documents.sent']), 'WebhookRequestDto'];
        yield 'deregister participant' => ['deregisterParticipant', $participant, 'ParticipantIdentifierDto'];
        yield 'push business card' => ['pushBusinessCard', $participant, 'ParticipantIdentifierDto'];
    }

    #[Test]
    #[DataProvider('conflictingBusinessCards')]
    public function business_card_formats_are_mutually_exclusive(string $requestClass, bool $typed): void
    {
        $card = $typed ? new CompleteBusinessCard([new BusinessEntity([new MultilingualName('Example')], 'BE')]) : [];
        $this->expectException(InvalidArgumentException::class);

        if ($requestClass === ParticipantRegistrationRequest::class) {
            new ParticipantRegistrationRequest(ParticipantIdentifier::of('0208:0123456789'), 'BE', [], $card);
        } else {
            new BusinessCardUpdateRequest(ParticipantIdentifier::of('0208:0123456789'), [], $card);
        }
    }

    public static function conflictingBusinessCards(): iterable
    {
        foreach ([ParticipantRegistrationRequest::class, BusinessCardUpdateRequest::class] as $class) {
            yield $class . ' array' => [$class, false];
            yield $class . ' DTO' => [$class, true];
        }
    }

    #[Test]
    public function legacy_and_array_business_cards_remain_supported_individually(): void
    {
        $id = ParticipantIdentifier::of('0208:0123456789');
        $legacy = ['name' => 'Example'];
        $complete = ['businessEntity' => [['name' => [['value' => 'Example']], 'countryCode' => 'BE']]];

        $this->assertSame($legacy, (new ParticipantRegistrationRequest($id, 'BE', $legacy))->toArray()['businessCardInfo']);
        $this->assertSame($legacy, (new BusinessCardUpdateRequest($id, $legacy))->toArray()['businessCardInfo']);
        $this->assertSame($complete, (new ParticipantRegistrationRequest($id, 'BE', completeBusinessCard: $complete))->toArray()['completeBusinessCard']);
        $this->assertSame($complete, (new BusinessCardUpdateRequest($id, completeBusinessCard: $complete))->toArray()['completeBusinessCard']);
    }

    #[Test]
    #[DataProvider('emptyBusinessCardLists')]
    public function typed_business_cards_require_the_lists_mandated_by_the_api(string $class): void
    {
        $this->expectException(InvalidArgumentException::class);

        $class === CompleteBusinessCard::class ? new CompleteBusinessCard([]) : new BusinessEntity([], 'BE');
    }

    public static function emptyBusinessCardLists(): iterable
    {
        yield [CompleteBusinessCard::class];
        yield [BusinessEntity::class];
    }

    #[Test]
    public function it_exposes_client_information_and_optional_fields(): void
    {
        $body = json_encode([
            'ulid' => '01CLIENT',
            'status' => 'ACTIVE',
            'creationTimestamp' => '2026-11-12T10:00:00Z',
            'lastModifiedTimestamp' => '2026-11-12T10:00:00Z',
            'subscriptionCancelled' => false,
            'clientReference' => 'client-ref',
            'organizationReference' => 'org-ref',
            'apiableSubscriptionId' => 'subscription',
            'apiableInformation' => ['apiablePlanId' => 'plan'],
            'customAttributes' => (object) ['department' => 'invoicing'],
        ]);
        $this->assertMatchesSchema(json_decode($body), 'peppol', 'ClientInfoResponseDto');
        $client = $this->mockClient([new Response(200, [], $body), new Response(200, [], $body)]);
        $this->assertSame('01CLIENT', $client->getClientInfo()['ulid']);
        $info = $client->api()->client->infoDto();
        $this->assertSame('plan', $info->apiablePlanId);
        $this->assertSame('subscription', $info->apiableSubscriptionId);
        $this->assertFalse($info->subscriptionCancelled);
        $this->assertSame(['department' => 'invoicing'], $info->customAttributes);
        $this->assertSame('https://peppol.example.test/v1/clients', (string) $this->history[1]['request']->getUri());
    }

    #[Test]
    public function it_exposes_mandatory_document_and_process_identifiers_without_losing_the_icd_prefix(): void
    {
        $item = ['documentTypeIdentifier' => ['value' => 'doc', 'scheme' => 'busdox-docid-qns'], 'processIdentifier' => ['value' => 'process']];
        $this->assertMatchesSchema(json_decode(json_encode($item)), 'peppol', 'MandatoryDocumentTypeResponseDto');
        $client = $this->mockClient([new Response(200, [], json_encode([$item])), new Response(200, [], '[]')]);
        $types = $client->api()->participants->mandatoryDocumentTypesDto('0208');
        $this->assertSame('doc', $types->items[0]->documentTypeIdentifier->value);
        $this->assertSame('busdox-docid-qns', $types->items[0]->documentTypeIdentifier->scheme);
        $this->assertSame('process', $types->items[0]->processIdentifier->value);
        $this->assertSame([], $client->getMandatoryDocumentTypes('0208'));
        $this->assertSame('/v1/participant-registrations/mandatory-document-types', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('icd=0208', $this->history[0]['request']->getUri()->getQuery());
    }
}

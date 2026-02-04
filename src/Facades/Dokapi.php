<?php

namespace AndyFraussen\Dokapi\Facades;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \AndyFraussen\Dokapi\Api\DokapiApi api()
 * @method static string getStatus()
 * @method static \AndyFraussen\Dokapi\Dto\TextResponse getStatusDto()
 * @method static array createWebhook(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static \AndyFraussen\Dokapi\Dto\CreateWebhookResponse createWebhookDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static array listWebhooks()
 * @method static \AndyFraussen\Dokapi\Dto\WebhookCollection listWebhooksDto()
 * @method static array deleteWebhook(string $ulid)
 * @method static \AndyFraussen\Dokapi\Dto\DeleteWebhookResponse deleteWebhookDto(string $ulid)
 * @method static string generateWebhookSecret()
 * @method static \AndyFraussen\Dokapi\Dto\TextResponse generateWebhookSecretDto()
 * @method static array getClientCounters(string $fromDate, string $toDate, ?string $flow = null)
 * @method static \AndyFraussen\Dokapi\Dto\ClientCounterCollection getClientCountersDto(string $fromDate, string $toDate, ?string $flow = null)
 * @method static array deactivateClientRegistration()
 * @method static \AndyFraussen\Dokapi\Dto\ClientCancellationResponse deactivateClientRegistrationDto()
 * @method static array sendOutgoingDocument(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $metadata, string $xmlContent)
 * @method static \AndyFraussen\Dokapi\Dto\OutgoingDocumentResponse createOutgoingDocumentDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $metadata)
 * @method static \AndyFraussen\Dokapi\Dto\OutgoingDocumentResponse sendOutgoingDocumentDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $metadata, string $xmlContent)
 * @method static array createOutgoingDocument(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $metadata)
 * @method static void uploadDocument(string $uploadUrl, string $xmlContent)
 * @method static array createValidatingDocument(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload = [])
 * @method static \AndyFraussen\Dokapi\Dto\ValidatingDocumentResponse createValidatingDocumentDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload = [])
 * @method static array sendValidatingDocument(string $xmlContent, array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload = [])
 * @method static \AndyFraussen\Dokapi\Dto\ValidatingDocumentResponse sendValidatingDocumentDto(string $xmlContent, array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload = [])
 * @method static array lookupParticipant(string $value, ?string $scheme = null)
 * @method static \AndyFraussen\Dokapi\Dto\ServiceGroupAndBusinessCard lookupParticipantDto(string $value, ?string $scheme = null)
 * @method static array findParticipant(string $value, ?string $scheme = null)
 * @method static \AndyFraussen\Dokapi\Dto\ParticipantRegistration findParticipantDto(string $value, ?string $scheme = null)
 * @method static array listParticipantRegistrations(?string $lastEvaluatedKey = null, ?int $limit = null)
 * @method static \AndyFraussen\Dokapi\Dto\ParticipantRegistrationPage listParticipantRegistrationsDto(?string $lastEvaluatedKey = null, ?int $limit = null)
 * @method static array registerParticipant(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static \AndyFraussen\Dokapi\Dto\CreateParticipantRegistrationResponse|\AndyFraussen\Dokapi\Dto\ProblemDetail registerParticipantDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static string deregisterParticipant(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static \AndyFraussen\Dokapi\Dto\TextResponse deregisterParticipantDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static string updateBusinessCard(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static \AndyFraussen\Dokapi\Dto\TextResponse updateBusinessCardDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static string pushBusinessCard(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static \AndyFraussen\Dokapi\Dto\TextResponse pushBusinessCardDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static array registerDocumentType(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static \AndyFraussen\Dokapi\Dto\RegisterDocumentTypeResponse registerDocumentTypeDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static string deregisterDocumentType(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static \AndyFraussen\Dokapi\Dto\TextResponse deregisterDocumentTypeDto(array|\AndyFraussen\Dokapi\Requests\PayloadInterface $payload)
 * @method static string generateIncomingPresignedUrl(string $documentUlid)
 * @method static \AndyFraussen\Dokapi\Dto\TextResponse generateIncomingPresignedUrlDto(string $documentUlid)
 * @method static string confirmIncomingDocument(string $documentUlid)
 * @method static \AndyFraussen\Dokapi\Dto\TextResponse confirmIncomingDocumentDto(string $documentUlid)
 * @method static bool verifyWebhookSignature(string $payload, string $signature, string $secret)
 *
 * @see \AndyFraussen\Dokapi\Clients\DokapiClient
 */
class Dokapi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DokapiClient::class;
    }
}

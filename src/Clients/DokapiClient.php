<?php

namespace AndyFraussen\Dokapi\Clients;

use AndyFraussen\Dokapi\Api\DokapiApi;
use AndyFraussen\Dokapi\Dto\ClientCancellationResponse;
use AndyFraussen\Dokapi\Dto\ClientCounterCollection;
use AndyFraussen\Dokapi\Dto\CreateParticipantRegistrationResponse;
use AndyFraussen\Dokapi\Dto\CreateWebhookResponse;
use AndyFraussen\Dokapi\Dto\DeleteWebhookResponse;
use AndyFraussen\Dokapi\Dto\OutgoingDocumentResponse;
use AndyFraussen\Dokapi\Dto\ParticipantRegistration;
use AndyFraussen\Dokapi\Dto\ParticipantRegistrationPage;
use AndyFraussen\Dokapi\Dto\RegisterDocumentTypeResponse;
use AndyFraussen\Dokapi\Dto\ServiceGroupAndBusinessCard;
use AndyFraussen\Dokapi\Dto\TextResponse;
use AndyFraussen\Dokapi\Dto\ValidatingDocumentResponse;
use AndyFraussen\Dokapi\Dto\WebhookCollection;
use AndyFraussen\Dokapi\Exceptions\DokapiAuthException;
use AndyFraussen\Dokapi\Exceptions\DokapiClientException;
use AndyFraussen\Dokapi\Exceptions\DokapiException;
use AndyFraussen\Dokapi\Exceptions\DokapiNotFoundException;
use AndyFraussen\Dokapi\Exceptions\DokapiRateLimitException;
use AndyFraussen\Dokapi\Exceptions\DokapiRequestException;
use AndyFraussen\Dokapi\Exceptions\DokapiServerException;
use AndyFraussen\Dokapi\Exceptions\DokapiValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

use Psr\Http\Message\ResponseInterface;

class DokapiClient
{
    protected array $config;
    protected Client $http;
    protected ?CacheRepository $cache;
    protected ?DokapiApi $api = null;

    public function __construct(array $config, ?Client $http = null, ?CacheRepository $cache = null)
    {
        $this->config = $config;
        $this->http = $http ?? new Client([
            'base_uri' => $config['base_url'] ?? null,
        ]);
        $this->cache = $cache;
    }

    public function api(): DokapiApi
    {
        if ($this->api === null) {
            $this->api = new DokapiApi($this);
        }

        return $this->api;
    }

    public function getStatus(): string
    {
        return $this->requestText('GET', '/status', [], false);
    }

    public function getStatusDto(): TextResponse
    {
        return TextResponse::fromString($this->getStatus());
    }

    public function createWebhook(array $payload): array
    {
        return $this->requestJson('POST', '/webhooks', ['json' => $payload]);
    }

    public function createWebhookDto(array $payload): CreateWebhookResponse
    {
        return CreateWebhookResponse::fromArray($this->createWebhook($payload));
    }

    public function listWebhooks(): array
    {
        return $this->requestJson('GET', '/webhooks');
    }

    public function listWebhooksDto(): WebhookCollection
    {
        return WebhookCollection::fromArray($this->listWebhooks());
    }

    public function deleteWebhook(string $ulid): array
    {
        return $this->requestJson('DELETE', "/webhooks/{$ulid}");
    }

    public function deleteWebhookDto(string $ulid): DeleteWebhookResponse
    {
        return DeleteWebhookResponse::fromArray($this->deleteWebhook($ulid));
    }

    public function generateWebhookSecret(): string
    {
        return $this->requestText('POST', '/webhooks/secretKey');
    }

    public function generateWebhookSecretDto(): TextResponse
    {
        return TextResponse::fromString($this->generateWebhookSecret());
    }

    public function getClientCounters(string $fromDate, string $toDate, ?string $flow = null): array
    {
        $query = ['fromDate' => $fromDate, 'toDate' => $toDate];
        if ($flow) {
            $query['flow'] = $flow;
        }

        return $this->requestJson('GET', '/client-counter', ['query' => $query]);
    }

    public function getClientCountersDto(string $fromDate, string $toDate, ?string $flow = null): ClientCounterCollection
    {
        return ClientCounterCollection::fromArray($this->getClientCounters($fromDate, $toDate, $flow));
    }

    public function deactivateClientRegistration(): array
    {
        return $this->requestJson('POST', '/clients/deactivate-client-registration');
    }

    public function deactivateClientRegistrationDto(): ClientCancellationResponse
    {
        return ClientCancellationResponse::fromArray($this->deactivateClientRegistration());
    }

    /**
     * Create an outgoing document and upload the XML to the pre-signed URL.
     *
     * @param array $metadata Required keys: sender, receiver, c1CountryCode, documentTypeIdentifier, processIdentifier
     * @param string $xmlContent UBL XML content to upload
     * @return array The create response payload
     * @throws DokapiException
     */
    public function sendOutgoingDocument(array $metadata, string $xmlContent): array
    {
        $response = $this->createOutgoingDocument($metadata);
        $uploadUrl = $response['preSignedUploadUrl'] ?? null;

        if (!$uploadUrl) {
            throw new DokapiException('Dokapi did not return a preSignedUploadUrl.');
        }

        $this->uploadDocument($uploadUrl, $xmlContent);

        return $response;
    }

    public function send(array $payload, string $xmlContent): array
    {
        return $this->sendOutgoingDocument($payload, $xmlContent);
    }

    /**
     * Create an outgoing document to obtain a pre-signed upload URL.
     */
    public function createOutgoingDocument(array $metadata): array
    {
        return $this->requestJson('POST', '/outgoing-peppol-documents', ['json' => $metadata]);
    }

    /**
     * Upload the XML to the provided pre-signed URL.
     */
    public function uploadDocument(string $uploadUrl, string $xmlContent): void
    {
        $this->requestRaw('PUT', $uploadUrl, [
            'headers' => [
                'Content-Type' => 'application/xml',
            ],
            'body' => $xmlContent,
        ], false);
    }

    public function createValidatingDocument(array $payload = []): array
    {
        return $this->requestJson('POST', '/validating-peppol-documents', ['json' => $payload]);
    }

    public function sendValidatingDocument(string $xmlContent, array $payload = []): array
    {
        $response = $this->createValidatingDocument($payload);
        $uploadUrl = $response['preSignedUploadUrl'] ?? null;

        if (!$uploadUrl) {
            throw new DokapiException('Dokapi did not return a preSignedUploadUrl for validation.');
        }

        $this->uploadDocument($uploadUrl, $xmlContent);

        return $response;
    }

    public function createOutgoingDocumentDto(array $metadata): OutgoingDocumentResponse
    {
        return OutgoingDocumentResponse::fromArray(
            $this->createOutgoingDocument($metadata)
        );
    }

    public function sendOutgoingDocumentDto(array $metadata, string $xmlContent): OutgoingDocumentResponse
    {
        return OutgoingDocumentResponse::fromArray(
            $this->sendOutgoingDocument($metadata, $xmlContent)
        );
    }

    public function createValidatingDocumentDto(array $payload = []): ValidatingDocumentResponse
    {
        return ValidatingDocumentResponse::fromArray(
            $this->createValidatingDocument($payload)
        );
    }

    public function sendValidatingDocumentDto(string $xmlContent, array $payload = []): ValidatingDocumentResponse
    {
        return ValidatingDocumentResponse::fromArray(
            $this->sendValidatingDocument($xmlContent, $payload)
        );
    }

    public function lookupParticipantDto(string $value, ?string $scheme = null): ServiceGroupAndBusinessCard
    {
        return ServiceGroupAndBusinessCard::fromArray($this->lookupParticipant($value, $scheme));
    }

    public function findParticipantDto(string $value, ?string $scheme = null): ParticipantRegistration
    {
        return ParticipantRegistration::fromArray($this->findParticipant($value, $scheme));
    }

    public function listParticipantRegistrationsDto(?string $lastEvaluatedKey = null, ?int $limit = null): ParticipantRegistrationPage
    {
        return ParticipantRegistrationPage::fromArray(
            $this->listParticipantRegistrations($lastEvaluatedKey, $limit)
        );
    }

    public function registerParticipantDto(array $payload): CreateParticipantRegistrationResponse
    {
        return CreateParticipantRegistrationResponse::fromArray($this->registerParticipant($payload));
    }

    public function deregisterParticipantDto(array $payload): TextResponse
    {
        return TextResponse::fromString($this->deregisterParticipant($payload));
    }

    public function updateBusinessCardDto(array $payload): TextResponse
    {
        return TextResponse::fromString($this->updateBusinessCard($payload));
    }

    public function pushBusinessCardDto(array $payload): TextResponse
    {
        return TextResponse::fromString($this->pushBusinessCard($payload));
    }

    public function registerDocumentTypeDto(array $payload): RegisterDocumentTypeResponse
    {
        return RegisterDocumentTypeResponse::fromArray($this->registerDocumentType($payload));
    }

    public function deregisterDocumentTypeDto(array $payload): TextResponse
    {
        return TextResponse::fromString($this->deregisterDocumentType($payload));
    }

    public function generateIncomingPresignedUrlDto(string $documentUlid): TextResponse
    {
        return TextResponse::fromString($this->generateIncomingPresignedUrl($documentUlid));
    }

    public function confirmIncomingDocumentDto(string $documentUlid): TextResponse
    {
        return TextResponse::fromString($this->confirmIncomingDocument($documentUlid));
    }

    public function lookupParticipant(string $value, ?string $scheme = null): array
    {
        $query = ['value' => $value];
        if ($scheme) {
            $query['scheme'] = $scheme;
        }

        return $this->requestJson('GET', '/participant-registrations/lookup', ['query' => $query]);
    }

    public function findParticipant(string $value, ?string $scheme = null): array
    {
        $query = ['value' => $value];
        if ($scheme) {
            $query['scheme'] = $scheme;
        }

        return $this->requestJson('GET', '/participant-registrations/find', ['query' => $query]);
    }

    public function listParticipantRegistrations(?string $lastEvaluatedKey = null, ?int $limit = null): array
    {
        $query = [];
        if ($lastEvaluatedKey) {
            $query['lastEvaluatedKey'] = $lastEvaluatedKey;
        }
        if ($limit !== null) {
            $query['limit'] = $limit;
        }

        return $this->requestJson('GET', '/participant-registrations', ['query' => $query]);
    }

    public function registerParticipant(array $payload): array
    {
        return $this->requestJson('POST', '/participant-registrations', ['json' => $payload]);
    }

    public function deregisterParticipant(array $payload): string
    {
        return $this->requestText('DELETE', '/participant-registrations', ['json' => $payload]);
    }

    public function updateBusinessCard(array $payload): string
    {
        return $this->requestText('PUT', '/participant-registrations/business-cards', ['json' => $payload]);
    }

    public function pushBusinessCard(array $payload): string
    {
        return $this->requestText('POST', '/participant-registrations/business-cards/push', ['json' => $payload]);
    }

    public function registerDocumentType(array $payload): array
    {
        return $this->requestJson('POST', '/participant-registrations/documents', ['json' => $payload]);
    }

    public function deregisterDocumentType(array $payload): string
    {
        return $this->requestText('DELETE', '/participant-registrations/documents', ['json' => $payload]);
    }

    public function generateIncomingPresignedUrl(string $documentUlid): string
    {
        return $this->requestText('POST', "/incoming-peppol-documents/{$documentUlid}/generate-presigned-url");
    }

    public function confirmIncomingDocument(string $documentUlid): string
    {
        return $this->requestText('POST', "/incoming-peppol-documents/{$documentUlid}/confirm");
    }

    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computed, $signature);
    }

    protected function getAccessToken(): string
    {
        $staticToken = $this->config['access_token'] ?? null;
        if (is_string($staticToken) && $staticToken !== '') {
            return $staticToken;
        }

        $cacheKey = 'dokapi.access_token';
        if (($this->config['cache_token'] ?? true) && $this->cache) {
            $cached = $this->cache->get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $clientId = $this->config['client_id'] ?? null;
        $clientSecret = $this->config['client_secret'] ?? null;
        $tokenUrl = $this->config['token_url'] ?? null;

        if (!$clientId || !$clientSecret || !$tokenUrl) {
            throw new DokapiException('Dokapi credentials are not configured.');
        }

        $decoded = $this->requestJson(
            'POST',
            $tokenUrl,
            [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ],
            false
        );

        if (empty($decoded['access_token'])) {
            throw new DokapiException('Dokapi token response did not include access_token.');
        }

        $token = (string) $decoded['access_token'];
        $expiresIn = (int) ($decoded['expires_in'] ?? 3600);

        if (($this->config['cache_token'] ?? true) && $this->cache) {
            $ttl = max(60, $expiresIn - 60);
            $this->cache->put($cacheKey, $token, $ttl);
        }

        return $token;
    }

    protected function requestJson(string $method, string $path, array $options = [], bool $withAuth = true): array
    {
        $response = $this->requestRaw($method, $path, $options, $withAuth);
        $body = (string) $response->getBody();

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new DokapiException('Dokapi response was not valid JSON.');
        }

        return $decoded;
    }

    protected function requestText(string $method, string $path, array $options = [], bool $withAuth = true): string
    {
        $response = $this->requestRaw($method, $path, $options, $withAuth);
        return (string) $response->getBody();
    }

    protected function requestRaw(string $method, string $path, array $options = [], bool $withAuth = true): ResponseInterface
    {
        $baseOptions = [
            'timeout' => $this->config['timeout'] ?? 30,
            'connect_timeout' => $this->config['connect_timeout'] ?? 10,
            'verify' => $this->config['verify'] ?? true,
            'http_errors' => false,
        ];

        if ($withAuth) {
            $token = $this->getAccessToken();
            $headers = $options['headers'] ?? [];
            $headers['Authorization'] = 'Bearer ' . $token;
            $options['headers'] = $headers;
        }

        $requestOptions = array_merge($baseOptions, $options);

        try {
            $response = $this->http->request($method, $path, $requestOptions);
        } catch (GuzzleException $e) {
            throw new DokapiException('Dokapi request failed: ' . $e->getMessage(), 0, $e);
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $body = (string) $response->getBody();
            throw $this->mapHttpException($statusCode, $body);
        }

        return $response;
    }

    protected function mapHttpException(int $statusCode, string $body): DokapiRequestException
    {
        return match (true) {
            $statusCode === 401 || $statusCode === 403 => new DokapiAuthException('Dokapi authentication failed', $statusCode, $body),
            $statusCode === 404 => new DokapiNotFoundException('Dokapi resource not found', $statusCode, $body),
            $statusCode === 400 || $statusCode === 422 => new DokapiValidationException('Dokapi validation failed', $statusCode, $body),
            $statusCode === 429 => new DokapiRateLimitException('Dokapi rate limit exceeded', $statusCode, $body),
            $statusCode >= 500 => new DokapiServerException('Dokapi server error', $statusCode, $body),
            default => new DokapiClientException('Dokapi request failed', $statusCode, $body),
        };
    }
}

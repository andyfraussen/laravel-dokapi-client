# Laravel Dokapi Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/andyfraussen/laravel-dokapi-client.svg?style=flat-square)](https://packagist.org/packages/andyfraussen/laravel-dokapi-client)
[![Total Downloads](https://img.shields.io/packagist/dt/andyfraussen/laravel-dokapi-client.svg?style=flat-square)](https://packagist.org/packages/andyfraussen/laravel-dokapi-client)
[![License](https://img.shields.io/packagist/l/andyfraussen/laravel-dokapi-client.svg?style=flat-square)](LICENSE.md)

A fluent Laravel client for the **Dokapi Peppol API**, with optional Belgian Mandate onboarding. Built for PHP 8.3+ and Laravel 12/13.

## Introduction

Dokapi for Laravel provides a high-level, expressive interface for interacting with the Dokapi Peppol infrastructure. It abstracts away the complexities of OAuth2 authentication, token management, and raw API calls, allowing you to focus on building your application.

- **Developer Experience First:** A fluent, discoverable API that feels native to Laravel.
- **Type Safety:** Extensive use of DTOs ensures your IDE understands every response.
- **Production Ready:** Built-in OAuth2 caching, signature verification, and granular error handling.
- **Future Proof:** Fully optimized for PHP 8.3+ and Laravel 12/13.

## Installation

You may install the package via Composer:

```bash
composer require andyfraussen/laravel-dokapi-client
```

Next, you should publish the configuration file:

```bash
php artisan vendor:publish --provider="AndyFraussen\Dokapi\DokapiServiceProvider" --tag="dokapi-config"
```

## Configuration

After publishing the configuration, you may define your Dokapi credentials in your `.env` file:

```env
DOKAPI_CLIENT_ID=your-client-id
DOKAPI_CLIENT_SECRET=your-client-secret
DOKAPI_BASE_URL=https://peppol-api.dokapi-stg.io/v1
DOKAPI_MANDATE_BASE_URL=
DOKAPI_TOKEN_URL=https://dev-portal.dokapi.io/api/oauth2/token
DOKAPI_ACCESS_TOKEN=
DOKAPI_WEBHOOK_SECRET=
DOKAPI_CACHE_TOKEN=true
DOKAPI_TIMEOUT=30
DOKAPI_CONNECT_TIMEOUT=10
DOKAPI_VERIFY=true
DOKAPI_USER_AGENT=andyfraussen/laravel-dokapi-client
```

The default `DOKAPI_BASE_URL` points to the staging environment defined in Dokapi's OpenAPI spec. Use the production base URL provided by Dokapi when you move to production.
If you already have an access token, set `DOKAPI_ACCESS_TOKEN` to bypass OAuth2.

For advanced HTTP tuning (proxies, custom headers, TLS options), you can pass Guzzle options via the `dokapi.http` config key.

Mandate support requires an explicit `DOKAPI_MANDATE_BASE_URL`. Use the endpoint for your subscription and environment; the [current Mandate schema](https://mandate-api.dokapi.io/v1/openapi.json) lists `https://mandate-api.dokapi.io/v1`. Setting this value does not change the Peppol endpoint. Both APIs use the configured OAuth credentials or static access token.

## Usage

### The Fluent API

The recommended way to interact with Dokapi is through the fluent API provided by the `Dokapi` facade.

```php
use AndyFraussen\Dokapi\Facades\Dokapi;

// Send a document with a single call
$response = Dokapi::api()->outgoingDocuments->sendDto($payload, $xml);

echo $response->document->ulid;
```

### Outgoing Documents

You can easily send Peppol documents using our expressive request builders:

```php
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Requests\OutgoingDocumentRequest;

$request = new OutgoingDocumentRequest(
    sender: ParticipantIdentifier::of('0208:0123456789'),
    receiver: ParticipantIdentifier::of('0208:9876543210'),
    c1CountryCode: 'BE',
    documentTypeIdentifier: $docType,
    processIdentifier: $process,
    externalReference: 'inv-2026-001'
);

$response = Dokapi::sendOutgoingDocument($request, $xml);
```

The send methods create a document and upload its XML. Successful upload starts asynchronous processing; delivery is reported through the webhook, not the return value.

### Recovering from upload failures

If document creation succeeds but the upload fails, `sendOutgoingDocument()`, `sendValidatingDocument()` and their fluent/DTO variants throw `DokapiUploadException`:

```php
use AndyFraussen\Dokapi\Exceptions\DokapiUploadException;

try {
    $response = Dokapi::sendOutgoingDocument($request, $xml);
} catch (DokapiUploadException $exception) {
    $documentUlid = $exception->getDocumentUlid();
    $created = $exception->getCreateResponse();
    $uploadUrl = $exception->getUploadUrl();
    // Store this context in your delivery record and reconcile the existing document.
    // The original HTTP or transport exception is available through getPrevious().
    throw $exception;
}
```

Do not retry the complete send method automatically: each call creates another document, and a timeout can leave the upload outcome uncertain. The package does not replay document creation or upload requests. For durable workflows, use `createOutgoingDocumentDto()`, persist the creation response, and then call `uploadDocument()` separately. Keep presigned URLs private.

### Client information and mandatory document types

```php
$client = Dokapi::api()->client->infoDto();
echo $client->status;

// Keep the ICD as a string to preserve leading zeroes.
$types = Dokapi::api()->participants->mandatoryDocumentTypesDto('0208');
foreach ($types->items as $type) {
    $documentType = $type->documentTypeIdentifier;
    $process = $type->processIdentifier;
}
```

Raw-array variants are available as `info()` and `mandatoryDocumentTypes()`, or through `getClientInfo()` and `getMandatoryDocumentTypes()` on the facade/client.

### Complete business cards

Prefer `completeBusinessCard`; Dokapi deprecates the simplified `businessCardInfo` format. Arrays remain supported, and typed builders are also available:

```php
use AndyFraussen\Dokapi\Dto\BusinessCards\BusinessEntity;
use AndyFraussen\Dokapi\Dto\BusinessCards\CompleteBusinessCard;
use AndyFraussen\Dokapi\Dto\BusinessCards\MultilingualName;
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Requests\BusinessCardUpdateRequest;

$card = new CompleteBusinessCard([
    new BusinessEntity(
        name: [new MultilingualName('Example Company BV', 'nl')],
        countryCode: 'BE',
    ),
]);

Dokapi::api()->participants->updateBusinessCard(new BusinessCardUpdateRequest(
    participantIdentifier: ParticipantIdentifier::of('0208:0123456789'),
    completeBusinessCard: $card,
));
```

`BusinessIdentifier` and `BusinessContact` cover optional identifiers and contacts. `ParticipantRegistrationRequest` accepts the same card. Both request constructors reject supplying `businessCardInfo` and `completeBusinessCard` together, including empty arrays.

### Belgian Mandate onboarding

Dokapi's [Connect workflow](https://dev-portal.dokapi.io/docs/overview_connect) uses mandates to manage authorization and participant onboarding. An active receiving capability drives participant and mandatory document-type registration. The package supports requesting Belgian mandates, retrieving mandates, listing pages, uploading KYC proof, and parsing mandate/capability feedback. Other countries' creation endpoints and mandate updates/inactivation are not wrapped yet.

Configure `DOKAPI_MANDATE_BASE_URL`, then register the events your application needs **before** creating a mandate:

```php
use AndyFraussen\Dokapi\Requests\WebhookRequest;

$webhooks = Dokapi::api()->mandates->webhooks();
$webhooks->create(new WebhookRequest('https://your-app.example/dokapi/webhook', [
    'mandate.requested',
    'mandate.reviewed',
    'mandate.action-required',
    'mandate.capability.active',
    'mandate.capability.action-required',
    'mandate.capability.inactive',
]));
```

Generate an HMAC secret once using `$webhooks->secret()` and store it as `DOKAPI_WEBHOOK_SECRET`. Calling that method again rotates the secret; it is not a read operation.

```php
use AndyFraussen\Dokapi\Dto\Mandates\KycCompany;
use AndyFraussen\Dokapi\Dto\Mandates\KycData;
use AndyFraussen\Dokapi\Dto\Mandates\KycRepresentative;
use AndyFraussen\Dokapi\Dto\Mandates\ParticipantAgreement;
use AndyFraussen\Dokapi\Dto\Mandates\PeppolEntity;
use AndyFraussen\Dokapi\Dto\Mandates\PersonalInformation;
use AndyFraussen\Dokapi\Dto\Mandates\PostalAddress;
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Requests\BelgiumMandateRequest;

$person = new PersonalInformation('Jane', 'Doe');
$request = new BelgiumMandateRequest(
    startDate: now()->toDateString(),
    kbo: '0123456789',
    kycData: new KycData(
        company: new KycCompany(
            name: 'Example Company BV',
            address: new PostalAddress('Main Street 12', 'Antwerp', '2000', 'Antwerp', 'BE'),
            contactEmail: 'invoicing@example.test',
        ),
        representative: new KycRepresentative($person, '1982-11-05', 'jane@example.test', 'Director'),
        participantAgreement: new ParticipantAgreement(now()->toDateString(), '1.0', true, $person),
    ),
    peppolEntities: [new PeppolEntity(
        ParticipantIdentifier::of('0208:0123456789'),
        sendingCapabilities: true,
        receivingCapabilities: true,
    )],
);

$response = Dokapi::api()->mandates->requestBelgiumDto($request);
// Persist the mandate ULID and creation response before uploading any proof.
$mandateUlid = $response->mandate->ulid;

if ($response->additionalDocumentUpload !== null) {
    Dokapi::api()->mandates->uploadAdditionalDocument($response->additionalDocumentUpload, $kycProofBytes);
}

$mandate = Dokapi::api()->mandates->getDto($mandateUlid);
$page = Dokapi::api()->mandates->listDto(limit: 15);
$nextPage = $page->lastEvaluatedKey === null
    ? null
    : Dokapi::api()->mandates->listDto($page->lastEvaluatedKey, 15);
```

KYC requirements depend on the subscription's contract type. Third-party contracts require representative and agreement data plus an upload; their creation response reports `PENDING_UPLOAD`. Dokapi contracts can omit those two objects and start in `REQUESTED`. An upload URL expires after 50 minutes and cannot currently be regenerated. `uploadAdditionalDocument()` accepts raw bytes, defaults to `application/pdf`, supports an explicit content type, and sends no API credentials or redirects. Uploaded document keys are available in `kycData->additionalDocuments` and are omitted when serializing KYC data into requests.

Mandate request dates must use `YYYY-MM-DD`, an optional end date must be after the start date, and at most ten explicit entities are accepted. Country/contract-specific rules remain enforced by Dokapi, including the future start date required for any French `0225` entity.

Mandate and capability states are separate. Use each capability's state and scope when deciding whether sending or receiving is enabled:

```php
use AndyFraussen\Dokapi\Enums\MandateCapabilityScope;

foreach ($mandate->capabilities as $capability) {
    if ($capability->scope === MandateCapabilityScope::Receiving && $capability->isActive()) {
        // Receiving is active for $capability->participantIdentifier.
    }
}
```

Known statuses, scopes and webhook events use backed enums. Unknown future values remain strings so a new Dokapi state does not prevent reading a response. The raw-array variants of the mandate operations are also available, and `Dokapi::forMandates()` provides a client for the configured Mandate API, including its webhook management and token refresh.

### Mandate webhook feedback

Verify the exact raw request bytes before decoding:

```php
$feedback = Dokapi::api()->webhooks->parseMandateFeedback(
    $request->getContent(),
    (string) $request->header('X-Dokapi-Signature'),
    (string) config('dokapi.webhook_secret'),
);
```

Invalid signatures, an empty secret and malformed payloads throw `DokapiException`. `body` is a `MandateCapability` for `mandate.capability.*` events and `MandateStatusInformation` for mandate-level events. The envelope's `ulid` identifies the invocation for deduplication; `body->ulid` identifies the mandate or capability, and capability feedback also exposes `body->mandateUlid` and `body->clientUlid`.

Durably enqueue valid feedback before returning an empty `200 OK`, process it asynchronously, and deduplicate by invocation ULID. Dokapi retries failed deliveries; this package parses feedback but does not register a route, enqueue jobs, or persist deduplication records for your application. Use webhooks to observe progress rather than polling.

### Error Handling

API requests and standalone uploads throw a `DokapiRequestException` subclass for non-2xx responses (auth, validation, not found, rate limit, server, or client errors). You can access the raw response body with `getResponseBody()` or a parsed `ProblemDetail` (when available) with `getProblemDetail()`. High-level send methods wrap failures after document creation in `DokapiUploadException`; inspect `getPrevious()` for the underlying error and retain the creation context as described above.

Participant registrations can also return a 207 response with a `ProblemDetail` payload. In that case, `registerDto()` returns a `ProblemDetail` instance instead of a success DTO.

A 401 from an authenticated API call evicts only that API's cached OAuth token and still throws `DokapiAuthException`. The next explicit call obtains a new token. A 403 does not evict the token, and requests are never automatically replayed. To manage tokens explicitly, use `Dokapi::forgetAccessToken()` or `Dokapi::refreshAccessToken()`; for the Mandate API use `Dokapi::forMandates()->refreshAccessToken()`. Static access tokens are returned unchanged and must be replaced in configuration when invalid.

### Webhook Signature Verification

Security is paramount. Verify incoming webhooks with ease:

```php
use AndyFraussen\Dokapi\Facades\Dokapi;

$signature = $request->header('X-Dokapi-Signature');
$secret = config('dokapi.webhook_secret');

$isValid = is_string($signature)
    && is_string($secret)
    && $secret !== ''
    && Dokapi::api()->webhooks->verifySignature($request->getContent(), $signature, $secret);
```

## Testing

```bash
composer test
```

Contract tests use pinned, offline snapshots of Dokapi's published schemas and sample responses. See [tests/Fixtures/README.md](tests/Fixtures/README.md) for provenance and normalization details. They exercise HTTP serialization and DTO mapping without calling authenticated APIs. CI covers PHP 8.3/8.4 with Laravel 12 and PHP 8.5 with Laravel 13, and runs dependency auditing alongside tests and static analysis.

## Static Analysis

We maintain a high standard of code quality using Larastan (PHPStan):

```bash
composer phpstan
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

# Laravel Dokapi Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/andyfraussen/laravel-dokapi-client.svg?style=flat-square)](https://packagist.org/packages/andyfraussen/laravel-dokapi-client)
[![Total Downloads](https://img.shields.io/packagist/dt/andyfraussen/laravel-dokapi-client.svg?style=flat-square)](https://packagist.org/packages/andyfraussen/laravel-dokapi-client)
[![License](https://img.shields.io/packagist/l/andyfraussen/laravel-dokapi-client.svg?style=flat-square)](LICENSE.md)

A sleek, fluent, and strongly-typed Laravel client for the **Dokapi Peppol API**. Built for modern PHP 8.5+ and Laravel 12 environments.

## Introduction

Dokapi for Laravel provides a high-level, expressive interface for interacting with the Dokapi Peppol infrastructure. It abstracts away the complexities of OAuth2 authentication, token management, and raw API calls, allowing you to focus on building your application.

- **Developer Experience First:** A fluent, discoverable API that feels native to Laravel.
- **Type Safety:** Extensive use of DTOs ensures your IDE understands every response.
- **Production Ready:** Built-in OAuth2 caching, signature verification, and granular error handling.
- **Future Proof:** Fully optimized for PHP 8.5 and Laravel 12.

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
DOKAPI_BASE_URL=https://peppol-api.dokapi.io/v1
```

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

### Webhook Signature Verification

Security is paramount. Verify incoming webhooks with ease:

```php
use AndyFraussen\Dokapi\Facades\Dokapi;

$isValid = Dokapi::webhooks()->verifySignature(
    payload: $request->getContent(),
    signature: $request->header('X-Dokapi-Signature'),
    secret: config('dokapi.webhook_secret')
);
```

## Testing

```bash
composer test
```

## Static Analysis

We maintain a high standard of code quality using Larastan (PHPStan):

```bash
composer phpstan
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

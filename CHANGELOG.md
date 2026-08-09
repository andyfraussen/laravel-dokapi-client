# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [Unreleased]

## [2.0.0] - 2026-08-09
### Removed
- Support for Laravel 11 and `orchestra/testbench` 9. Laravel 11 security support ended on
  12 March 2026 and Composer blocks its framework releases because of active advisories.

### Added
- Support for Laravel 13 and `orchestra/testbench` 11.
- `DOKAPI_WEBHOOK_SECRET` environment variable and the matching `dokapi.webhook_secret`
  config key, which the documented webhook verification snippet already assumed.
- CI workflow covering PHP 8.4 / Laravel 12 and PHP 8.5 / Laravel 13.

### Changed
- Presigned document uploads no longer inherit `dokapi.http` options or the default headers
  of an injected Guzzle client, and no longer follow redirects.
- `uploadDocument()` rejects upload URLs that are not absolute HTTPS URLs.
- Access tokens are no longer cached when the reported `expires_in` cannot outlive the
  60-second safety margin, or when it is missing or non-numeric.
- Minimum `guzzlehttp/guzzle` raised to `^7.8.2`.

### Fixed
- API request URLs preserve a path prefix configured in `base_url`, such as the `/v1` in the
  documented default. Previously Guzzle's `base_uri` resolution discarded it, so every call
  went to the host root.
- Dynamic webhook and incoming-document path segments are URL-encoded, so a ULID containing
  path traversal or query characters can no longer be used to reach a different authenticated
  endpoint.
- Typed Dokapi exceptions are preserved when `dokapi.http.http_errors` is enabled in config.
  Previously that setting let Guzzle's own `RequestException` escape instead of the mapped
  `DokapiValidationException`, `DokapiNotFoundException`, and friends.
- Token responses whose `access_token` is missing, blank, or not a string are rejected rather
  than cast to a useless string.

### Upgrade notes
This release changes behaviour in ways that can break a working integration. Check each item.

- **Laravel 11 is no longer supported.** Upgrade to Laravel 12 or 13 before taking 2.0.0.
- **Presigned uploads must use HTTPS.** `uploadDocument()` now throws `DokapiException` for
  `http://` or relative URLs. If you point local or CI environments at an `http://` object
  store such as MinIO or LocalStack, terminate TLS in front of it or stub the upload.
- **Presigned uploads ignore `dokapi.http`.** Uploads previously inherited that array. If you
  set `proxy` there, uploads now attempt a direct connection and will fail behind a
  restricted egress proxy. The same applies to `cert` and `ssl_key`. API requests are
  unaffected.
- **Presigned uploads no longer follow redirects,** so that a redirect cannot replay document
  XML to another host. If your object store answers a `PUT` with a 3xx — S3 does this for a
  bucket in a region other than the endpoint's — the upload now raises the mapped exception
  for that status instead of silently following it.
- **Request URLs change if `base_url` carries a path prefix.** With the documented
  `https://peppol-api.dokapi-stg.io/v1`, calls now go to `/v1/...` rather than `/...`. Any
  test double, recorded fixture, or firewall rule that matched the old prefix-less paths
  needs updating.
- **Short-lived tokens are no longer cached.** A Dokapi deployment issuing tokens with
  `expires_in` at or below 60 seconds will now see one token request per API call.

## [1.1.0] - 2026-02-04
### Added
- ProblemDetail DTO and structured error exposure for request exceptions.
- Support for request DTOs via `PayloadInterface` in client methods.
- HTTP configuration passthrough and optional user agent configuration.
- Tests for 207 ProblemDetail handling, payload acceptance, and token caching.

### Changed
- Participant registration DTO handling to reflect documented 207 responses.
- Improved JSON/text parsing to handle empty bodies and JSON-encoded strings.
- Token cache key now scoped by client and endpoints.

### Fixed
- Cache resolution now prefers the cache factory when available.
- Documentation now matches supported PHP/Laravel versions and Dokapi staging defaults.

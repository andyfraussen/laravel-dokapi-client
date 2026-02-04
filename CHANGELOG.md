# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [Unreleased]

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

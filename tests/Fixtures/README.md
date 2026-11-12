# Dokapi API contract fixtures

These fixtures are pinned to the public specifications retrieved on 12 November 2026:

- [Peppol OpenAPI](https://peppol-api.dokapi-stg.io/v1/openapi.json)
- [Mandate OpenAPI](https://mandate-api.dokapi.io/v1/openapi.json)

Each `*.schema.json` records the source URL, retrieval date and SHA-256 of the original
download. It includes the transitive schema dependencies used by the contract tests;
tests never download specifications or call Dokapi.

The schema snapshots retain field types, required fields, enums and constraints.
Descriptions, examples and deprecation annotations are omitted. Two explicit
normalizations make OpenAPI 3.0 schemas usable with the JSON Schema validator:

1. `nullable: true` becomes a type union with `null` (or an `anyOf` for a reference).
2. The Mandate specification uses `oneOf` for five structurally overlapping country KYC
   response schemas. A valid company matches multiple alternatives, including in Dokapi's
   own samples. Those KYC unions alone use `anyOf` in the snapshot. Identifier unions
   retain `oneOf`; their required country-specific keys distinguish them.

The request, creation, retrieval and page JSON files come from the Mandate specification's
published examples. Webhook examples combine the documented envelope and required fields
with the corresponding sample mandate/capability. They contain example data, not account data.

When refreshing fixtures, download both public specifications, update the source hashes,
extract the relevant schemas and referenced dependencies, apply the normalizations above,
and review changes against the SDK before replacing the samples. Run `composer test` and
`composer phpstan`. A passing snapshot test verifies the pinned contract; it does not prove
subscription permissions or live acceptance by Dokapi.

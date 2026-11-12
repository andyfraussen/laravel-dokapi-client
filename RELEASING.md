# Maintainer and Release Guide

## Access and handover

A release maintainer needs permission to push to the GitHub repository and publish
GitHub releases. Repository administrators manage private vulnerability reporting
and the Packagist webhook. Packagist maintainers can check package update failures
and registry access if the webhook stops working.

GitHub and Packagist access are managed in those services. Keep personal credentials
out of the repository. When adding a backup maintainer, grant access through the
services and confirm that they can inspect CI, prepare a release and handle private
security reports. Review access again when maintainers change.

## Validate a change

1. Review the diff and keep unrelated work out of the release.
2. Run `composer update --no-interaction --prefer-dist` to resolve a fresh development
   dependency set. This library intentionally ignores `composer.lock`; consumers
   resolve their own dependencies.
3. Run `composer validate --strict --no-check-publish`, `composer audit`,
   `composer test` and `composer phpstan`.
4. For API changes, review the relevant Dokapi specification and the pinned contract
   fixtures. [tests/Fixtures/README.md](tests/Fixtures/README.md) documents their
   provenance and normalization rules. Keep tests offline and use synthetic data.
5. Ensure GitHub CI passes all supported PHP/Laravel combinations. Local checks on
   one combination do not replace the full matrix.

GitHub Actions are pinned to full commit SHAs. Dependabot opens weekly update pull
requests for those pins. Before accepting an action update, verify that the commit
belongs to the upstream repository, retain its readable version comment, review
the upstream change and require passing CI. The workflow needs read-only repository
access and does not retain checkout credentials.

## Publish a version

1. Choose a version using Semantic Versioning. Changes to public signatures,
   exception handling or supported platform versions can require a major release.
2. Move the relevant `CHANGELOG.md` entries from **Unreleased** into a dated version
   section and include upgrade instructions for behavior changes. Update `SECURITY.md`
   if the supported major version changes. Composer derives the package version from
   Git tags; do not add a `version` field to `composer.json`.
3. Commit and push the release changes to `main`. Confirm that CI passed for the exact
   commit to be tagged, and prepare the release notes in a local file.
4. Create and push an annotated tag for that tested commit. Replace the placeholders
   below with the intended version, full commit SHA and release-notes path:

   ```bash
   git tag -a vX.Y.Z FULL_TESTED_COMMIT_SHA -m "Release vX.Y.Z"
   git push origin refs/tags/vX.Y.Z
   gh release create vX.Y.Z --repo andyfraussen/laravel-dokapi-client --verify-tag --title "vX.Y.Z" --notes-file /path/to/release-notes.md
   ```

5. Verify the GitHub release is published, its tag resolves to the tested commit,
   and the working tree is clean. Never move an existing published tag; publish a
   new version for corrections.
6. The repository's push webhook notifies Packagist. Verify the new version appears
   in [Composer's package metadata](https://repo.packagist.org/p2/andyfraussen/laravel-dokapi-client.json)
   and that its source and distribution references match the tested commit. An
   accepted webhook alone does not prove the version has been indexed.

Repository policy and CI-only changes can be published on `main` without creating
a new package version.

## Security reports

Follow [SECURITY.md](SECURITY.md). Repository administrators should keep private
vulnerability reporting enabled. Review reports privately and coordinate any fix,
release and advisory before public disclosure.

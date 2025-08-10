# Unsend PHP Library Release Notes

## 1.1.0 - 2025-08-09

### Added

- `Unsend::create()` factory for simpler initialization
- `iterateEmails()` for seamless pagination
- Unified `ApiException` for non-2xx responses with parsed message/status
- PHPStan configuration and stricter array-shape PHPDocs

### Changed

- HTTP client defaults: timeouts, connect timeout, `Accept`, and `User-Agent`
- Only send `Content-Type: application/json` when a JSON body is present
- `Response` now tolerates empty bodies without throwing
- README updated for factory usage, iteration, and error behavior
- Tests updated to assert `ApiException`
- Composer metadata updated: require `ext-json`; add PHPStan to dev dependencies

[1.1.0]: https://github.com/mattstein/unsend-php/compare/1.0.0...1.1.0

## 1.0.0 - 2025-08-06

Initial release.

# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

(nothing yet)

## [0.8.0] - 2019-04-08

### Added

- Add portfolios and representations to Bib objects.

### Changed

- Update [scriptotek/marc](https://github.com/scriptotek/php-marc/) to version 2.0.
  Please see [the corresponding CHANGELOG](https://github.com/scriptotek/php-marc/blob/master/CHANGELOG.md#200---2018-10-23) for information about changes that might break your app if you work with MARC records.
- Update from HTTPlug to PSR-17/PSR-18 HTTP discovery.
  Note that a HTTP factory implementation is now required.
  Run `composer require http-interop/http-factory-guzzle` to add a HTTP Factory for Guzzle.
- Require PHP >= 7.1.

### Fixed

- Set MMS id on Bib object when initiated from SRU response.
- Handle more error responses.

## [0.7.2] - 2018-11-09

### Added

- Added methods for retrieving task lists (lending requests and requested resources).

### Fixed

- Fixed retrieval of Holding records: [#9](https://github.com/scriptotek/php-alma-client/issues/9)

## [0.7.1] - 2018-10-23

### Fixed

- Fixed infinite loop in `Users::search()` when search result only contained contacts.

## [0.7.0] - 2018-09-02

### Added

- Added options `limit` and `phrase` to `Users::search()`.
- Added methods `getBarcodes()` and `getUniversityIds()` to `UserIdentifiers` to get all active values.
  These are accessible on the `User` object as `$user->barcodes` and `$user->universityIds`.
- Added method `Client::items->fromBarcode(...)`
- Added method `Item::checkOut()`, `Item::loan()`, `Item:scanIn()` and `User::loans`
- Added method `exists()` to `Bib`, `Holding`, `Item`, `User`. If trying to get data from a non-existing
  resource, `Scriptotek\Facade\Exception\ResourceNotFound` is thrown.
- Added array access to `Bibs` and `Holdings`.
- Expanded the exception tree:
  - `ClientException` for all Facade Client errors
    - `RequestFailed` (new) for 4xx errors. `$e->getMessage()` returns the error message from the server.
      - `InvalidApiKey` (new)
      - `ResourceNotFound` (new) when a request resource was not found.
      - `InvalidQuery` (new) when the query was not understood by the server. Currently only used by the users api.
- Added interface to get libraries (`$client->libraries`) and locations (`$client->libraries[$libraryCode]->locations`).
- Added item checkout and scan-in.
- Analytics: The `getRows()` method is deprecated. Iterate over the `Report` instead.
- Made package auto-discoverable in Laravel 5.5+.
- Added helper method `Users::findOne()` to get the first match from a search.
- Added support for retrieving user requests from `Bib`, `Item` and `User`.

### Changed

- BC: Changed the signature of `Users::search()`.
  Change `users->search($query, $full, $batchSize)`
  to `users->search($query, ['expand' => $full, 'batchSize' => $batchSize])`.
- All exceptions now extend `Scriptotek\Facade\Exception\ClientException`.
- BC: Moved the user identifier logic from the `User` class to a new `UserIdentifiers` class.
  The `$user->barcode` and `$user->universityId` magic properties work as before, but
  `$user->getBarcode()`, `$user->getUniversityId()`, `$user->getIdOfType(...)` and `$user->getIds()`
  must now be called as `$user->identifiers->getBarcode()`, `$user->identifiers->getUniversityId()`,
  `$user->identifiers->firstOfType(...)` and `$user->identifiers->all()`.
  All methods now return only identifiers with status 'ACTIVE' by default.
  Removed the `$user->hasFullRecord()` method.
- Replaced/split the `ResourceList` base model into `Model`, `LazyResource` and `LazyResourceList`.
- Removed the `Bib::getXml()` method in favour of a standardized `getData()` method part
  of the new `Model` base model.
- Removed `$client->bibs->getHolding($mmsId, $holdingId)`, please use
  `$client->bibs[$mmsId]->holdings[$holdingId]` instead. It's lazy-loaded, so
  there's no performance drawback of the new interface.
- Analytics reports: Parses the new `saw-sql:columnHeading` attributes so that columns can be accessed
  by name.
- The `post()` and `put()` methods on `Client` now returns the response body.

### Fixed

- Ignore users without primary id in `Users::search()`.
  Previously, these that would crash the response handler.

## [0.6.1] - 2017-07-02

tbd.

[Unreleased]: https://github.com/scriptotek/php-marc/compare/v0.8.0...HEAD
[0.8.0]: https://github.com/scriptotek/php-marc/compare/v0.7.2...v0.8.0
[0.7.2]: https://github.com/scriptotek/php-marc/compare/v0.7.1...v0.7.2
[0.7.1]: https://github.com/scriptotek/php-marc/compare/v0.7.0...v0.7.1
[0.7.0]: https://github.com/scriptotek/php-marc/compare/v0.6.1...v0.7.0
[0.6.1]: https://github.com/scriptotek/php-marc/compare/v0.6.0...v0.6.1

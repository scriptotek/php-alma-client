# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [Unreleased]

### Added

- Added options `limit` and `phrase` to `Users::search()`.
- Added `InvalidQueryException` exception.
- Added methods `getBarcodes()` and `getUniversityIds()` to `UserIdentifiers` to get all active values.
  These are accessible on the `User` object as `$user->barcodes` and `$user->universityIds`.
- Added method `$client->items->fromBarcode(...)`
- Added method `exists()` to `Bib`, `Holding`, `Item`, `User`. If trying to get data from a non-existing
  resource, `Scriptotek\Alma\Exception\ResourceNotFound` is thrown.

### Changed

- BC: Changed the signature of `Users::search()`.
  Change `users->search($query, $full, $batchSize)`
  to `users->search($query, ['expand' => $full, 'batchSize' => $batchSize])`.
- All exceptions now extend `Scriptotek\Alma\Exception\ClientException`.
- BC: Moved the user identifier logic from the `User` class to a new `UserIdentifiers` class.
  The `$user->barcode` and `$user->universityId` magic properties work as before, but
  `$user->getBarcode()`, `$user->getUniversityId()`, `$user->getIdOfType(...)` and `$user->getIds()`
  must now be called as `$user->identifiers->getBarcode()`, `$user->identifiers->getUniversityId()`,
  `$user->identifiers->firstOfType(...)` and `$user->identifiers->all()`.
  All methods now return only identifiers with status 'ACTIVE' by default.
  Removed the `$user->hasFullRecord()` method.
- Replaced the `ResourceList` base model with `GhostModel` and `Model` for models that can/cannot
  be lazy-loaded respectively.
- Removed the `Bib::getXml()` method in favour of a standardized `getData()` method part
  of the new `Model` base model.
- Removed `$client->bibs->getHolding($mmsId, $holdingId)`, please use
  `$client->bibs[$mmsId]->holdings[$holdingId]` instead. It's lazy-loaded, so
  there's no performance drawback of the new interface.


### Fixed

- Ignore users without primary id in `Users::search()`.
  Previously, these that would crash the response handler.

## [0.6.1] - 2017-07-02

tbd.

[Unreleased]: https://github.com/scriptotek/php-marc/compare/v0.6.1...HEAD
[0.6.1]: https://github.com/scriptotek/php-marc/compare/v0.6.0...v0.6.1

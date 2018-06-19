# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [Unreleased]

### Added

- Added options `limit` and `phrase` to `Users::search()`.

### Changed

- BC: Changed the signature of `Users::search()`.
  Change `users->search($query, $full, $batchSize)`
  to `users->search($query, ['expand' => $full, 'batchSize' => $batchSize])`.
- All exceptions now extend `Scriptotek\Alma\Exception\ClientException`.

### Fixed

- Ignore users without primary id in `Users::search()`.
  Previously, these that would crash the response handler.

## [0.6.1] - 2017-07-02

tbd.

[Unreleased]: https://github.com/scriptotek/php-marc/compare/v0.6.1...HEAD
[0.6.1]: https://github.com/scriptotek/php-marc/compare/v0.6.0...v0.6.1

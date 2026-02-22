# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.3.1] - 2026-02-22

### Added

- added router unit tests (`tests/unit/Lib/RouterTest.php`)
- added template unit tests for switcher behavior (`tests/unit/TemplateTest.php`)
- added route attribute test coverage for optional arguments (`tests/unit/Lib/Routing/Attributes/RouteTest.php`)

### Changed

- updated package version to `4.3.1`
- updated runtime and development dependency constraints in `composer.json`
- updated lockfile dependencies to latest resolved versions (`composer.lock`)
- added `tests` composer script (`phpunit --testdox`)
- expanded PHPStan scan paths to include `examples` (`phpstan.neon`)
- improved `Template` switcher parsing and default switcher handling (`src/HemiFrame/Template.php`)
- updated multiple cache adapters for PSR simple-cache signature compatibility (`src/HemiFrame/Lib/Cache/*`)
- applied code quality and static-analysis cleanups across core library classes

## [4.2.1] - 2025-05-03

### Changed

- fixed bug in src/HemiFrame/Lib/Cache/Apc.php
- added new cache driver: Redis

## [4.1.2] - 2025-04-23

### Changed

- fixed: Notice: iconv(): Detected an illegal character in input string in...

## [4.1.1] - 2024-11-06

### Changed

- updated dependencies

## [4.1.0] - 2024-03-26

### Changed

- minimum PHP version is now 8.1
- added Inject attribute for dependency injection container
- fixed bugs in dependency injection container

## [4.0.1] - 2024-03-12

### Changed

- fixed bug in Dependency Injection Container

## [4.0.0] - 2024-02-08

### Added

- added attributes for routing and dependency injection container
- added phpunit tests

### Changed

- set minimum PHP version to 8.0
- updated dependencies
- format code with php-cs-fixer

### Removed

- removed GeoIp class

## [3.3.7] - 2023-10-03

### Added

- added @Singleton annotation

## [3.3.6] - 2023-05-04

### Changed

- fixed deprecated warning in src/HemiFrame/Template.php

## [3.3.5] - 2023-01-10

### Changed

- update annotations

## [3.3.4] - 2023-01-09

### Changed

- fix formatting

## [3.3.2] - 2021-11-10

### Changed

- fixed bug in src/HemiFrame/Template.php

## [3.3.1] - 2021-11-07

### Changed

- changes in src/HemiFrame/Lib/Image.php

## [3.1.6] - 2021-04-27

### Changed

- support PHP 8
- fixed small bugs

## [3.1.5] - 2020-06-08

### Changed

- updated cache interface

## [3.1.4] - 2020-05-27

### Changed

- fixed bugs

## [3.1.3] - 2020-05-25

### Changed

- fixed bugs

## [3.1.2] - 2020-05-25

### Changed

- fixed bugs

## [3.1.1] - 2020-05-25

### Changed

- implement psr/http-message in File class

### Removed

- remove Query Builder from DBSession

## [3.0.1] - 2020-05-25

### Added

- implement psr/simple-cache

### Changed

- optimize Template class

### Removed

- Query builder is moved to a new project

## [2.4.4] - 2020-05-20

### Added

- CHANGELOG.MD
- implement PHP Stan

# Laravel Impersonate Changelog

## 1.2.2 (2018-01-19)

### Changed

- Register Blade directives after resolving

### Fixed

- Blade directives documentation

## 1.2.1 (2017-09-03)

### Changed

- PHP version requirement
- Laravel version requirement

## 1.2.0 (2017-07-28)

### Added

- Laravel 5.5 compatibility
- Package auto-discovery

## 1.1.0 (2017-03-05)

### Added

- Custom Session guard driver based on the original Session Guard ([c76bb96](https://github.com/404labfr/laravel-impersonate/commit/c76bb96da9ca53b70fd3ce5d063722076ffcbcb4))

### Changed

- The Auth events `login`, `authenticated` and `logout` are not fired anymore when taking or leaving impersonation

### Fixed

- The user remember token is not touched when taking and leaving impersonation ([#11](https://github.com/404labfr/laravel-impersonate/issues/11))

## 1.0.11 (2017-03-05)

### Added

- New blade directive `canBeImpersonated` ([#12](https://github.com/404labfr/laravel-impersonate/issues/12))
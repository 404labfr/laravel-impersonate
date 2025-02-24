# Laravel Impersonate Changelog

## 1.7.7

- Laravel 12.x support (thanks to [erikn69](https://github.com/erikn69), [#216](https://github.com/404labfr/laravel-impersonate/pull/216))

## 1.7.6

- Supports for PHP 8.4 (thanks to [MarioGattolla](https://github.com/MarioGattolla), [#211](https://github.com/404labfr/laravel-impersonate/pull/211))
- Dynamic redirection after leaving impersonation (thanks to [adamjsturge](https://github.com/adamjsturge), [#210](https://github.com/404labfr/laravel-impersonate/pull/210))

## 1.7.5

- Laravel 11.x support (thanks to [erikn69](https://github.com/erikn69), [#195](https://github.com/404labfr/laravel-impersonate/pull/195))

## 1.7.4

- Laravel 10.x support (thanks to [freekmurze](https://github.com/freekmurze), [#175](https://github.
  com/404labfr/laravel-impersonate/pull/175))

## 1.7.3

- Laravel 9.x support (thanks to [freekmurze](https://github.com/freekmurze), [#149](https://github.com/404labfr/laravel-impersonate/pull/149))

## 1.7.2

### Added

- Supports for PHP 8.0 (thanks to [pascalbaljet](https://github.com/pascalbaljet), [#121](https://github.com/404labfr/laravel-impersonate/pull/121))

### Fixed

- getImpersonator() when working with multiple guards (thanks to [carsso](https://github.com/carsso)), [#120](https://github.com/404labfr/laravel-impersonate/pull/120))
- Can't leave impersonation when multi-guard is used (thanks to [ctf0](https://github.com/ctf0), [#116](https://github.com/404labfr/laravel-impersonate/pull/117))

### Changed

- Allow guard name in blade directives (thanks to [ctf0](https://github.com/ctf0), [#115](https://github.com/404labfr/laravel-impersonate/pull/117))
- Documentation about multi-guard usage (thanks to [ctf0](https://github.com/ctf0), [#117](https://github.com/404labfr/laravel-impersonate/pull/117))

### Removed

- composer.lock

## 1.7.1

### Added

- Laravel 8.x support

## 1.7.0

### Added

- `ImpersonateManager@findUserById` will now throw a `MissingUserProvider` exception when guard has no user provider [baa722b](https://github.com/404labfr/laravel-impersonate/commit/baa722b1bde0aefd9efbd9454c699b7894ddc49b)
- `ImpersonateManager@findUserById` will now throw a `InvalidUserProvider` exception when guard has an invalid user provider [baa722b](https://github.com/404labfr/laravel-impersonate/commit/baa722b1bde0aefd9efbd9454c699b7894ddc49b)

### Changed

- Helper `can_be_impersonated` now use model `getAuthIdentifier()` instead of hardcoded `id` column [#105](https://github.com/404labfr/laravel-impersonate/pull/105)
- Git attributes [#108](https://github.com/404labfr/laravel-impersonate/pull/108)

## 1.6.0

### Added

- Laravel 7.x support

### Removed

- Laravel 5.x support

## 1.5.1

### Changed

- Use `Illuminate\Contracts\Auth\Authenticatable@getAuthIdentifier` instead of `Illuminate\Database\Eloquent\Model@getKey` [#96](https://github.com/404labfr/laravel-impersonate/pull/96)
- PHPDoc updated

## 1.5.0

### Changed

- Events uses `Illuminate\Contracts\Auth\Authenticatable` instead of `Illuminate\Database\Eloquent\Model` [#92](https://github.com/404labfr/laravel-impersonate/pull/92)
- PHPDoc and return values for `ImpersonateManager`

### Fixed

- Security issue for `symfony/http-foundation` ([CVE-2019-18888](https://github.com/advisories/GHSA-xhh6-956q-4q69)) 

## 1.4.3

### Fixed

- `can_impersonate()` helper
- Tests for Blade directives

## 1.4.2

### Added

- `is_impersonating()`, `can_impersonate()` and `can_be_impersonated()` helpers

### Changed

- Blade directives now use helpers

## 1.4.1

### Fixed

- Laravel 6.0 dependencies compatibility
- dump() in ImpersonateManager.php

## 1.4.0

### Added

- Allows impersonation through multiple guards ([Pull 58](https://github.com/404labfr/laravel-impersonate/pull/58))
- Added the public method `getImpersonator` to `ImpersonateManager` ([Pull 69](https://github.com/404labfr/laravel-impersonate/pull/69))

### Changed

- Laravel 6.0 compatibility (min version is 5.8) 

### Fixed

- The user `remember_token` is now preserved ([Pull 71](https://github.com/404labfr/laravel-impersonate/pull/71))

## 1.3.0 (2019-02-28)

### Changed

- Laravel 5.8 compatibility (min version)

## 1.2.3 (2018-09-03)

### Changed

- Documentation
- Use `getSessionKey()` in `take()` method

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

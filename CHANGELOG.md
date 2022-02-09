# Release Notes

## [v0.3.1 (2022-02-09)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.3.0...0.3.1)

### Added
- Added support for Laravel 9
- Added PHP 8.1 tests for Laravel 8 + 9

## [v0.3.0 (2022-01-03)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.2.1...0.3.0)

### Added
- Added primary key to entities relation table
- Added extra query scopes for Vouchers:  
  `Voucher::withoutPrefix()`  
  `Voucher::withoutSuffix()`  
  `Voucher::withoutStarted()`  
  `Voucher::withoutExpired()`  
  `Voucher::withoutRedeemed()`  
  `Voucher::withoutRedeemable()`
- Added missing type hints where possible

### Changed
- Replaced Travis with Github Actions

### Deprecated
- Dropped support for PHP 7.1 - 7.3

## [v0.2.1 (2021-08-10)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.2.0...0.2.1)

### Added
- Added callback to `Vouchers::redeemable()` for conditional checks

## [v0.2.0 (2021-04-04)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.13...0.2.0)

This release adds an owner field to vouchers, changing a lot of the handling around related entities.  
Please read the [upgrade guide](UPGRADING.md) for implications of this change.

### Added
- Added strict types declaration to all files
- Added owner field to vouchers
- Added query scopes for Voucher owner:  
  `Voucher::withEntities()`  
  `Voucher::withOwnerType()`  
  `Voucher::withOwner()`  
  `Voucher::withoutOwner()`
- Added migration command `vouchers:migrate` for converting owning related entities to proper owners

### Changed
- Changed voucher scopes to use table prefix in where clauses

### Update
- Updated morph map handling to use `Model::getMorphClass()`
- Updated PHP CS Fixer configuration

### Deprecated
- Removed deprecated methods `HasRedeemers::getRedeemers()` and `HasVouchers::getVouchers()`

## [v0.1.13 (2021-01-05)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.12...0.1.13)

### Added
- Added support for PHP 8

## [v0.1.12 (2020-09-09)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.11...0.1.12)

### Added
- Added support for Laravel 8
- Added PHP CS Fixer configuration

## [v0.1.11 (2020-09-04)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.10...0.1.11)

### Fixed
- Fixed PHPUnit 9.1+ deprecation warning
- Fixed morph maps not working with `Voucher::getEntities()`

### Optimization
- Prefixed non-namespaced function calls with backslash

### Deprecated
- Trigger explicit errors on deprecated methods

## [v0.1.10 (2020-03-05)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.9...0.1.10)

### Added
- Added support for Laravel 7
- Added support for PHPUnit 9
- Added matrix testing in Travis for relevant Laravel and PHP combinations

## [v0.1.9 (2020-03-05)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.8...0.1.9)

### Added
- Added PHP 7.4 to Travis
- Added Voucher model helpers: `Voucher::hasPrefix()` and `Voucher::hasSuffix()`
- Added query scopes for all Voucher helpers:  
  `Voucher::withPrefix()`  
  `Voucher::withSuffix()`  
  `Voucher::withStarted()`  
  `Voucher::withExpired()`  
  `Voucher::withRedeemed()`  
  `Voucher::withRedeemable()`

## [v0.1.8 (2020-01-03)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.7...0.1.8)

### Fixed
- Fixed PHP 7.1 regression

## [v0.1.7 (2020-01-03)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.6...0.1.7)

### Added
- Added code coverage using PHP Debugger (`phpdbg`) - see [testing](https://github.com/FrittenKeeZ/laravel-vouchers#testing) for usage
- Added `HasVouchers::createVoucher()` and `HasVouchers::createVouchers()` convenience methods

## [v0.1.6 (2019-12-13)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.5...0.1.6)

### Added
- Added `Voucher::$redeemer` which will hold the active redeemer during events  
  Enables vouchers to only be redeemed by related redeemer ([#1](https://github.com/FrittenKeeZ/laravel-vouchers/issues/1), [#2](https://github.com/FrittenKeeZ/laravel-vouchers/issues/2))

## [v0.1.5 (2019-12-13)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.4...0.1.5)

### Added
- Added proper vouchers relationship with `HasVouchers::vouchers()`

### Deprecated
- Deprecated `HasRedeemers::getRedeemers()` - use relationship instead
- Deprecated `HasVouchers::getVouchers()` - use relationship instead

## [v0.1.4 (2019-12-12)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.3...0.1.4)

### Added
- Added support for Laravel 6 ([#3](https://github.com/FrittenKeeZ/laravel-vouchers/issues/3))

### Changed
- Replaced array and string helpers with facade calls

## [v0.1.3 (2019-07-01)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.2...0.1.3)

### Fixed
- Fixed bug with incorrect return type

## [v0.1.2 (2019-01-17)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.1...0.1.2)

### Added
- Added support for Laravel 5.8

## [v0.1.1 (2019-01-17)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/0.1.0...0.1.1)

### Added
- Added support for Laravel 5.7

## [v0.1.0 (2018-07-03)](https://github.com/FrittenKeeZ/laravel-vouchers/compare/7e7e409...0.1.0)

Initial development release.

# Laravel Vouchers

[![Packagist](https://img.shields.io/packagist/v/FrittenKeeZ/laravel-vouchers.svg?style=flat-square)](https://packagist.org/packages/frittenkeez/laravel-vouchers)
[![Downloads](https://img.shields.io/packagist/dt/FrittenKeeZ/laravel-vouchers.svg?style=flat-square)](https://packagist.org/packages/frittenkeez/laravel-vouchers)
[![License](https://img.shields.io/github/license/FrittenKeeZ/laravel-vouchers.svg?style=flat-square)](LICENSE)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/FrittenKeeZ/laravel-vouchers/Tests)](https://github.com/FrittenKeeZ/laravel-vouchers/actions)

## Table of Contents
- [Installation](#installation)
- [Upgrading](#upgrading)
- [Changelog](#changelog)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Generate Codes](#generate-codes)
    - [Create Vouchers](#create-vouchers)
    - [Redeem Vouchers](#redeem-vouchers)
    - [Options](#options)
    - [Events](#events)
    - [Traits](#traits)
    - [Helpers](#helpers)
    - [Scopes](#scopes)
- [Testing](#testing)
- [License](#license)

## Installation
Install this package via Composer:
```bash
$ composer require frittenkeez/laravel-vouchers
```

## Upgrading
Please read the [upgrade guide](UPGRADING.md).

## Changelog
Please read the [release notes](CHANGELOG.md).

## Configuration
Publish config using Artisan command:
```bash
$ php artisan vendor:publish --tag=config --provider="FrittenKeeZ\Vouchers\VouchersServiceProvider"
```
Don't forget to run migrations:
```bash
$ php artisan migrate
```
Change basic configuration through `config/vouchers.php` - it should be well documented, so no need to describe all options here.

## Usage
This package comes with an ease-of-use facade `Vouchers` with FQN `FrittenKeeZ\Vouchers\Facades\Vouchers`.

### Generate Codes
Generating codes without checking if they exist; defaults from config will be used if not specified.
```php
Vouchers::generate(string|null $mask = null, string|null $characters = null): string;

$code = Vouchers::generate('***-***-***', '1234567890');
```
Batch generation of codes is also possible; these will be checked against existing codes.
```php
Vouchers::batch(int amount): array;

$codes = Vouchers::batch(10);
```

### Create Vouchers
Generating one or more vouchers is just as simple.
```php
Vouchers::create(int $amount = 1): object|array;

$voucher = Vouchers::create();
$vouchers = Vouchers::create(10);
```

### Redeem Vouchers
Redeeming vouchers requires that one provides a redeemer entity.  
Additional metadata for the redeemer can be provided.
```php
Vouchers::redeem(string $code, Illuminate\Database\Eloquent\Model $entity, array $metadata = []): bool;

try {
    $success = Vouchers::redeem('123-456-789', $user, ['foo' => 'bar']);
} catch (FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException $e) {
    // Code provided did not match any vouchers in the database.
} catch (FrittenKeeZ\Vouchers\Exceptions\VoucherAlreadyRedeemedException $e) {
    // Voucher has already been redeemed.
}
```

### Options
Besides defaults specified in `config/vouchers.php`, one can override options when generating codes or creating vouchers.  
Following methods apply to `Vouchers::generate()`, `Vouchers::batch()` and `Vouchers::create()` calls.
```php
// Override characters list.
Vouchers::withCharacters(string $characters);
// Override code mask.
Vouchers::withMask(string $mask);
// Override code prefix.
Vouchers::withPrefix(string $prefix);
// Disable code prefix.
Vouchers::withoutPrefix();
// Override code suffix.
Vouchers::withSuffix(string $suffix);
// Disable code suffix.
Vouchers::withoutSuffix();
// Override prefix and suffix separator.
Vouchers::withSeparator(string $separator);
// Disable prefix and suffix separator.
Vouchers::withoutSeparator();
```
Following methods only apply to `Vouchers::create()` call.
```php
// Add metadata to voucher.
Vouchers::withMetadata(array $metadata);
// Set voucher start time.
Vouchers::withStartTime(DateTime $timestamp);
// Set voucher start time using interval.
Vouchers::withStartTimeIn(DateInterval $interval);
// Set voucher start date - time component is zeroed.
Vouchers::withStartDate(DateTime $timestamp);
// Set voucher start date using interval - time component is zeroed.
Vouchers::withStartDateIn(DateInterval $interval);
// Set voucher expire time.
Vouchers::withExpireTime(DateTime $timestamp);
// Set voucher expire time using interval.
Vouchers::withExpireTimeIn(DateInterval $interval);
// Set voucher expire date - time component is set to end of day (23:59:59).
Vouchers::withExpireDate(DateTime $timestamp);
// Set voucher expire date using interval - time component is set to end of day (23:59:59).
Vouchers::withExpireDateIn(DateInterval $interval);
// Set related entities to voucher.
Vouchers::withEntities(Illuminate\Database\Eloquent\Model ...$entities);
// Set owning entity for voucher.
Vouchers::withOwner(Illuminate\Database\Eloquent\Model $owner);
```
All calls are chainable and dynamic options will be reset when calling `Vouchers::create()` or `Vouchers::reset()`.
```php
$voucher = Vouchers::withMask('***-***-***')
    ->withMetadata(['foo' => 'bar'])
    ->withExpireDateIn(CarbonInterval::create('P30D'))
    ->create();
$voucher = Vouchers::withOwner($user)->withPrefix('USR');
```

### Events
During events `Voucher::$redeemer` will be set to the active redeemer (`FrittenKeeZ\Vouchers\Models\Redeemer`).

By default vouchers will be marked as redeemed after one use, which is not always the desired outcome.  
To allow a voucher to be redeemed multiple times, subscribe to the `FrittenKeeZ\Vouchers\Models\Voucher::shouldMarkRedeemed()` event.
```php
Voucher::shouldMarkRedeemed(function (Voucher $voucher) {
    // Do some fancy checks here.
    return false;
});
```
To prevent a voucher from being redeemed altogether, subscribe to the `FrittenKeeZ\Vouchers\Models\Voucher::redeeming()` event.
```php
Voucher::redeeming(function (Voucher $voucher) {
    // Do some fancy checks here.
    return false;
});
```
To prevent a voucher from being redeemed by anyone but the related user.
```php
Voucher::redeeming(function (Voucher $voucher) {
    return $voucher->redeemer->redeemer->is($voucher->owner);
});
/* ... */
$voucher = Vouchers::withOwner($user)->create();
Vouchers::redeem($voucher->code, $user);
```
To perform additional actions after a vouchers has been redeemed, subscribe to the `FrittenKeeZ\Vouchers\Models\Voucher::redeemed()` event.
```php
Voucher::redeemed(function (Voucher $voucher) {
    // Do some additional stuff here.
});
```

### Traits
For convenience we provide some traits for fetching vouchers and redeemers for related entities, fx. users.  
`FrittenKeeZ\Vouchers\Concerns\HasRedeemers`
```php
// Associated redeemers relationship.
HasRedeemers::redeemers(): MorphMany;
// Get all associated redeemers.
$redeemers = $user->redeemers;
```
`FrittenKeeZ\Vouchers\Concerns\HasVouchers`
```php
// Owned vouchers relationship.
HasVouchers::vouchers(): MorphMany;
// Get all owned vouchers.
$vouchers = $user->vouchers;

// Associated vouchers relationship.
HasVouchers::associatedVouchers(): MorphToMany;
// Get all associated vouchers.
$vouchers = $user->associatedVouchers;

// Associated voucher entities relationship.
HasVouchers::voucherEntities(): MorphMany;
// Get all associated voucher entities.
$entities = $user->voucherEntities;
```
You can also create vouchers owned by an entity using these convenience methods.
```php
HasVouchers::createVoucher(Closure|null $callback = null): object;

// Without using callback.
$voucher = $user->createVoucher();
// With using callback.
$voucher = $user->createVoucher(function (FrittenKeeZ\Vouchers\Vouchers $vouchers) {
    $vouchers->withPrefix('USR');
});

HasVouchers::createVouchers(int $amount, Closure|null $callback = null): object|array;

// Without using callback.
$vouchers = $user->createVouchers(3);
// With using callback.
$vouchers = $user->createVouchers(3, function (FrittenKeeZ\Vouchers\Vouchers $vouchers) {
    $vouchers->withPrefix('USR');
});
```

### Helpers
Check whether a voucher code is redeemable without throwing any errors.
```php
Vouchers::redeemable(string $code, Closure|null $callback = null): bool;

// Without using callback.
$valid = Vouchers::redeemable('123-456-789');
// With using callback.
$valid = Vouchers::redeemable('123-456-789', function (FrittenKeeZ\Vouchers\Models\Voucher $voucher) {
    return $voucher->hasPrefix('foo');
});
```
Check whether a voucher code exists, optionally also checking a provided list.
```php
Vouchers::exists(string $code, array $codes = []): bool;

$exists = Vouchers::exists('123-456-789', ['987-654-321']);
```
Additional helpers methods on Voucher model.
```php
// Whether voucher has prefix, optionally specifying a separator different from config.
Voucher::hasPrefix(string $prefix, string|null $separator = null): bool;
// Whether voucher has suffix, optionally specifying a separator different from config.
Voucher::hasSuffix(string $suffix, string|null $separator = null): bool;
// Whether voucher is started.
Voucher::isStarted(): bool;
// Whether voucher is expired.
Voucher::isExpired(): bool;
// Whether voucher is redeemed.
Voucher::isRedeemed(): bool;
// Whether voucher is redeemable.
Voucher::isRedeemable(): bool;
```

### Scopes
For convenience we also provide Voucher scopes matching the helper methods.
```php
// Scope voucher query to a specific prefix, optionally specifying a separator different from config.
Voucher::withPrefix(string $prefix, string|null $separator = null);
// Scope voucher query to exclude a specific prefix, optionally specifying a separator different from config.
Voucher::withoutPrefix(string $prefix, string|null $separator = null);
// Scope voucher query to a specific suffix, optionally specifying a separator different from config.
Voucher::withSuffix(string $suffix, string|null $separator = null);
// Scope voucher query to exclude a specific suffix, optionally specifying a separator different from config.
Voucher::withoutSuffix(string $suffix, string|null $separator = null);
// Scope voucher query to started vouchers.
Voucher::withStarted();
// Scope voucher query to unstarted vouchers.
Voucher::withoutStarted();
// Scope voucher query to expired vouchers.
Voucher::withExpired();
// Scope voucher query to unexpired vouchers.
Voucher::withoutExpired();
// Scope voucher query to redeemed vouchers.
Voucher::withRedeemed();
// Scope voucher query to unredeemed vouchers.
Voucher::withoutRedeemed();
// Scope voucher query to redeemable vouchers.
Voucher::withRedeemable();
// Scope voucher query to unredeemable vouchers.
Voucher::withoutRedeemable();
// Scope voucher query to have voucher entities, optionally of a specific type (class or alias).
Voucher::withEntities(string|null $type = null);
// Scope voucher query to specific owner type (class or alias).
Voucher::withOwnerType(string $type);
// Scope voucher query to specific owner.
Voucher::withOwner(Illuminate\Database\Eloquent\Model $owner);
// Scope voucher query to no owners.
Voucher::withoutOwner();
```

## Testing
Running tests can be done either through composer, or directly calling the PHPUnit binary.
```bash
$ composer test
```
To run tests with code coverage, please make sure that `phpdbg` exists and is executable.
```bash
$ composer test-coverage
$ open tests/_reports/index.html
```

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.

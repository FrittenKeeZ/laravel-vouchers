# Upgrade Guide

## Table of Contents
- [0.8.x to 0.9.x](#08x-to-09x)
    - [Installation](#installation)
    - [Backward Incompatible Changes](#backward-incompatible-changes)
- [0.1.x to 0.2.x](#01x-to-02x)
    - [Installation](#installation)
    - [Migrating](#migrating)
    - [Backward Incompatible Changes](#backward-incompatible-changes)

## 0.8.x to 0.9.x
### Installation
Start by installing version `0.9.x`:
```bash
$ composer require frittenkeez/laravel-vouchers:^0.9.0
```

### Backward Incompatible Changes
#### Dropped support for Laravel 11
Laravel 11 is no longer supported - it is past its security support window and all `11.x` framework releases are flagged by security advisories, making them impossible to install without disabling composer's audit blocking.

Laravel `12.4` is now the minimum supported version, as that is where the `#[Scope]` attribute was introduced.

#### Model scopes now use the `#[Scope]` attribute
All query scopes have been converted from the `scopeXxx()` method prefix to the `#[Illuminate\Database\Eloquent\Attributes\Scope]` attribute. Calling the scopes is unchanged:
```php
// Still works exactly as before.
Voucher::withPrefix('FOO')->withoutExpired()->get();
```
If you have **extended** the `Voucher`, `VoucherEntity` or `Redeemer` models and overridden any scope method, you must rename the method and add the attribute:
```php
// Before
public function scopeWithPrefix(Builder $query, string $prefix): Builder

// After
#[Scope]
protected function withPrefix(Builder $query, string $prefix): Builder
```
Note that scope methods must **not** be `public` - a public method bypasses Eloquent's `__call()` forwarding, which would pass your first argument as the query builder. Use `protected` as shown above.

#### `withCode()` with an amount greater than one no longer throws
Creating multiple vouchers from a static code previously threw `InfiniteLoopException`, since every generated code was identical. A counter is now appended instead:
```php
// Before - threw InfiniteLoopException.
// After  - FIXED1, FIXED2, FIXED3.
Vouchers::withCode('FIXED')->create(3);
```
If you relied on the exception as a guard, note that `Vouchers::withMask()` with a static mask still behaves as before:
```php
// Still throws InfiniteLoopException.
Vouchers::withMask('FIXED')->create(3);
```
See the [Counter Codes](README.md#counter-codes) section for the full behaviour and the new `withCounter*()` options.

#### Metadata is cast to an array object
Metadata on `Voucher` and `Redeemer` is now cast to an `Illuminate\Database\Eloquent\Casts\ArrayObject` rather than a plain `array`, which allows it to be mutated in place:
```php
// Before - the whole array had to be reassigned.
$voucher->metadata = array_merge($voucher->metadata, ['amount' => 2]);

// After - mutating in place is persisted on save.
$voucher->metadata['amount'] = 2;
$voucher->save();
```
Reading values is unchanged, but anything passing metadata straight to an array function needs `toArray()`:
```php
// Before
array_merge($voucher->metadata, $extra);
count($voucher->metadata);

// After
array_merge($voucher->metadata->toArray(), $extra);
count($voucher->metadata); // Countable, so this still works.
```
Nested values can be mutated the same way, since chained array access writes through to the underlying array - see the [Metadata](README.md#metadata) section for details.

Assigning `null` still clears the column, and `whereNull('metadata')` keeps working. This is why the package ships its own `FrittenKeeZ\Vouchers\Casts\AsNullableArrayObject` rather than using Laravel's `AsArrayObject`, which would store the JSON string `'null'`.

#### The published migration uses `json` for metadata
The `metadata` columns in the published migration changed from `text` to `json`. This **only affects new installs** - existing installs keep their `text` columns and continue to work unchanged, since both store the same JSON payload.

SQLite is unaffected either way, as it maps `json` to `text`. If you want to align an existing MySQL or PostgreSQL install, alter the columns yourself:
```php
Schema::table('vouchers', function (Blueprint $table) {
    $table->json('metadata')->nullable()->change();
});
```
Be aware that altering a column to `json` fails if any existing row holds a value which is not valid JSON.

#### Redeem methods accept a voucher instance
`Vouchers::redeem()`, `Vouchers::unredeem()`, `Vouchers::redeemable()` and `Vouchers::unredeemable()` now accept either a voucher code or a `Voucher` instance. Passing an instance skips the code lookup query:
```php
// Both are valid.
Vouchers::redeem('123-456-789', $user);
Vouchers::redeem($voucher, $user);
```
An instance is used as-is, so its current in-memory state is trusted - refresh it first if it might be stale. An instance that does not exist in the database is treated as not found.

As a consequence the first parameter was renamed from `$code` to `$voucher`, which is only a breaking change if you call these methods using named arguments:
```php
// Before
Vouchers::unredeem(code: '123-456-789', callback: $callback);

// After
Vouchers::unredeem(voucher: '123-456-789', callback: $callback);
```

#### Renamed `Voucher::code()` scope to `Voucher::withCode()`
The `code` scope has been renamed to `withCode`, both to match the naming of the other scopes and because a scope named `code` would shadow the `code` column - Eloquent would resolve `$voucher->code` as a relationship method and throw an `ArgumentCountError` whenever the attribute was not loaded.
```php
// Before
Voucher::code('123-456-789')->first();

// After
Voucher::withCode('123-456-789')->first();
```

## 0.1.x to 0.2.x
### Installation
Start by installing version `0.2.x`:
```bash
$ composer require frittenkeez/laravel-vouchers:^0.2.0
```

Publish migration adding the new owner field using Artisan command:
```bash
$ php artisan vendor:publish --tag=migrations --provider="FrittenKeeZ\Vouchers\VouchersServiceProvider"
```
Don't forget to run migrations:
```bash
$ php artisan migrate
```

### Migrating
To ease the transition from the owning entity being mixed with all related entities, to directly using the new owner field, there's a console command you can use:
```bash
php artisan vouchers:migrate
```

#### Usage
By default, the migrate command will search for all models in the `app` and `app/Models` folders.
Only models using the `\FrittenKeeZ\Vouchers\Concerns\HasVouchers` trait will be taken into consideration.
Database operation mode defaults to `auto`, which means the related entity relationship will be removed, if there's only one present (the owning entity).

#### Options
Database operation mode `--mode=<mode>` has the following possibilities: `auto` (default), `retain` and `delete`.
Using `php artisan vouchers:migrate --mode=retain` will not remove any relationships, while `php artisan vouchers:migrate --mode=delete` will always remove the owning entity relationship.

It's also possible to specify other search folders, by using the `--folder=<folder>` option. Folders starting with `/` are considered absolute, otherwise they're considered relative to the project root.
Given you have a project located in `/Users/Me/Projects/Laravel`, with an additional subsystem called `Acme`, you can load both models from `app/Models` and `app/Acme/Models` in any of the following ways:
```bash
php artisan vouchers:migrate --folder="app/Models" --folder="app/Acme/Models"
php artisan vouchers:migrate --folder="app/Models" --folder="/Users/Me/Projects/Laravel/app/Acme/Models"
php artisan vouchers:migrate --folder="/Users/Me/Projects/Laravel/app/Models" --folder="/Users/Me/Projects/Laravel/app/Acme/Models"
```
Model namespace should be auto resolved, but only models using the `\FrittenKeeZ\Vouchers\Concerns\HasVouchers` trait will be taken into consideration.

Lastly, it's also possible to specify one or more models directly, which will circumvent the trait check.
You can do that by using the option `--model=<FQCN>` like so:
```bash
php artisan vouchers:migrate --model=App\\Acme\\Models\\User --model=App\\Acme\\Models\\Team
```

You can't combine both `--model` and `--folder`, specifying models explicitly with take precedence.

### Backward Incompatible Changes
#### Removed deprecated methods
- `\FrittenKeeZ\Vouchers\Concerns\HasRedeemers::getRedeemers()`
- `\FrittenKeeZ\Vouchers\Concerns\HasVouchers::getVouchers()`

#### Renamed methods
- `\FrittenKeeZ\Vouchers\Concerns\HasVouchers`:
    - `vouchers()` => `associatedVouchers()`

#### Logic Changes
Ensuring only that a related user can redeem a voucher has changed.
Previously you had to do this:
```php
Voucher::redeeming(function (Voucher $voucher) {
    return $voucher->redeemer->redeemer->is($voucher->getEntities(User::class)->first());
});
```
Now you have to do this:
```php
Voucher::redeeming(function (Voucher $voucher) {
    return $voucher->redeemer->redeemer->is($voucher->owner);
});
```

# Upgrade Guide

## Table of Contents
- [0.1.x to 0.2.x](#01x-to-02x)
    - [Installation](#installation)
    - [Migrating](#migrating)
    - [Backward Incompatible Changes](#backward-incompatible-changes)

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

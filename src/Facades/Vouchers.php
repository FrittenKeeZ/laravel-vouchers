<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array                                                                       getOptions()
 * @method static string                                                                      getCharacters()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withCharacters(?string $characters)
 * @method static string                                                                      getMask()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withMask(?string $mask)
 * @method static ?string                                                                     getPrefix()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withPrefix(?string $prefix)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withoutPrefix()
 * @method static ?string                                                                     getSuffix()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withSuffix(?string $suffix)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withoutSuffix()
 * @method static string                                                                      getSeparator()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withSeparator(?string $separator)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withoutSeparator()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withCode(string $code)
 * @method static ?array                                                                      getMetadata()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withMetadata(?array $metadata)
 * @method static ?\Carbon\Carbon                                                             getStartTime()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withStartTime(?\DateTime $timestamp)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withStartTimeIn(?\DateInterval $interval)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withStartDate(?\DateTime $timestamp)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withStartDateIn(?\DateInterval $interval)
 * @method static ?\Carbon\Carbon                                                             getExpireTime()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withExpireTime(?\DateTime $timestamp)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withExpireTimeIn(?\DateInterval $interval)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withExpireDate(?\DateTime $timestamp)
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withExpireDateIn(?\DateInterval $interval)
 * @method static array|\Illuminate\Database\Eloquent\Model[]                                 getEntities()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withEntities(iterable|\Illuminate\Database\Eloquent\Model $entities = [], \Illuminate\Database\Eloquent\Model ...$remaining)
 * @method static ?\Illuminate\Database\Eloquent\Model                                        getOwner()
 * @method static \FrittenKeeZ\Vouchers\Vouchers                                              withOwner(?\Illuminate\Database\Eloquent\Model $owner)
 * @method static \FrittenKeeZ\Vouchers\Config                                                getConfig()
 * @method static \FrittenKeeZ\Vouchers\Models\Voucher|\FrittenKeeZ\Vouchers\Models\Voucher[] create(int $amount = 1)
 * @method static bool                                                                        redeem(string $code, \Illuminate\Database\Eloquent\Model $entity, array $metadata = [])
 * @method static bool                                                                        redeemable(string $code, ?\Closure $callback = null)
 * @method static array|string[]                                                              batch(int $amount)
 * @method static string                                                                      generate(?string $mask = null, ?string $characters = null)
 * @method static string                                                                      wrap(string $str, ?string $prefix, ?string $suffix, string $separator)
 * @method static bool                                                                        exists(string $code, array $codes = [])
 * @method static void                                                                        reset()
 *
 * @see \FrittenKeeZ\Vouchers\Config
 * @see \FrittenKeeZ\Vouchers\Vouchers
 */
class Vouchers extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return 'vouchers';
    }
}

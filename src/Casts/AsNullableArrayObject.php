<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\Json;

/**
 * Fluent array object cast which preserves null.
 *
 * Eloquent skips JSON encoding of null for the built-in 'array' cast, but not for class
 * casts - so Laravel's own AsArrayObject stores null as the JSON string 'null', leaving a
 * nullable column never actually null and breaking whereNull() queries.
 */
class AsNullableArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     *
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Database\Eloquent\Casts\ArrayObject<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            /**
             * {@inheritdoc}
             */
            public function get($model, $key, $value, $attributes): ?ArrayObject
            {
                if (!isset($attributes[$key])) {
                    return null;
                }

                $data = Json::decode($attributes[$key]);

                return \is_array($data) ? new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS) : null;
            }

            /**
             * {@inheritdoc}
             */
            public function set($model, $key, $value, $attributes): array
            {
                return [$key => $value === null ? null : Json::encode($value)];
            }

            /**
             * {@inheritdoc}
             */
            public function serialize($model, string $key, $value, array $attributes): ?array
            {
                return $value?->getArrayCopy();
            }
        };
    }
}

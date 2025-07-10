<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Config
{
    /**
     * Dynamic options.
     */
    protected array $options = [];

    /**
     * Get dynamically set options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get code character list.
     */
    public function getCharacters(): string
    {
        return Arr::get($this->options, 'characters', config('vouchers.characters'));
    }

    /**
     * With code character list.
     */
    public function withCharacters(string $characters): self
    {
        Arr::set($this->options, 'characters', $characters);

        return $this;
    }

    /**
     * Get code mask.
     */
    public function getMask(): string
    {
        return Arr::get($this->options, 'mask', config('vouchers.mask'));
    }

    /**
     * With code mask.
     */
    public function withMask(string $mask): self
    {
        Arr::set($this->options, 'mask', $mask);

        return $this;
    }

    /**
     * Get code prefix.
     */
    public function getPrefix(): ?string
    {
        return Arr::get($this->options, 'prefix', config('vouchers.prefix'));
    }

    /**
     * With code prefix.
     */
    public function withPrefix(string $prefix): self
    {
        Arr::set($this->options, 'prefix', $prefix);

        return $this;
    }

    /**
     * Without code prefix.
     */
    public function withoutPrefix(): self
    {
        Arr::set($this->options, 'prefix', '');

        return $this;
    }

    /**
     * Get code suffix.
     */
    public function getSuffix(): ?string
    {
        return Arr::get($this->options, 'suffix', config('vouchers.suffix'));
    }

    /**
     * With code suffix.
     */
    public function withSuffix(string $suffix): self
    {
        Arr::set($this->options, 'suffix', $suffix);

        return $this;
    }

    /**
     * Without code suffix.
     */
    public function withoutSuffix(): self
    {
        Arr::set($this->options, 'suffix', '');

        return $this;
    }

    /**
     * Get separator for prefix and suffix.
     */
    public function getSeparator(): string
    {
        return Arr::get($this->options, 'separator', config('vouchers.separator'));
    }

    /**
     * With prefix and suffix separator.
     */
    public function withSeparator(string $separator): self
    {
        Arr::set($this->options, 'separator', $separator);

        return $this;
    }

    /**
     * Without prefix and suffix separator.
     */
    public function withoutSeparator(): self
    {
        Arr::set($this->options, 'separator', '');

        return $this;
    }

    /**
     * Get metadata.
     */
    public function getMetadata(): ?array
    {
        return Arr::get($this->options, 'metadata');
    }

    /**
     * With metadata.
     */
    public function withMetadata(array $metadata): self
    {
        Arr::set($this->options, 'metadata', $metadata);

        return $this;
    }

    /**
     * Get start time.
     */
    public function getStartTime(): ?Carbon
    {
        return Arr::get($this->options, 'starts_at');
    }

    /**
     * With start time.
     */
    public function withStartTime(?DateTime $timestamp): self
    {
        if ($timestamp === null) {
            Arr::forget($this->options, 'starts_at');
        } else {
            Arr::set($this->options, 'starts_at', Carbon::instance($timestamp));
        }

        return $this;
    }

    /**
     * With start time in the given interval.
     */
    public function withStartTimeIn(?DateInterval $interval): self
    {
        return $this->withStartTime($interval ? Carbon::now()->add($interval) : null);
    }

    /**
     * With start date - time component is set to 00:00:00.000000.
     */
    public function withStartDate(?DateTime $timestamp): self
    {
        return $this->withStartTime($timestamp ? Carbon::instance($timestamp)->startOfDay() : null);
    }

    /**
     * With start date in the given interval - time component is set to 00:00:00.000000.
     */
    public function withStartDateIn(?DateInterval $interval): self
    {
        return $this->withStartTime($interval ? Carbon::now()->add($interval)->startOfDay() : null);
    }

    /**
     * Get expire time.
     */
    public function getExpireTime(): ?Carbon
    {
        return Arr::get($this->options, 'expires_at');
    }

    /**
     * With expire time.
     */
    public function withExpireTime(?DateTime $timestamp): self
    {
        if ($timestamp === null) {
            Arr::forget($this->options, 'expires_at');
        } else {
            Arr::set($this->options, 'expires_at', Carbon::instance($timestamp));
        }

        return $this;
    }

    /**
     * With expire time in the given interval.
     */
    public function withExpireTimeIn(?DateInterval $interval): self
    {
        return $this->withExpireTime($interval ? Carbon::now()->add($interval) : null);
    }

    /**
     * With expire date - time component is set to 23:59:59.999999.
     */
    public function withExpireDate(?DateTime $timestamp): self
    {
        return $this->withExpireTime($timestamp ? Carbon::instance($timestamp)->endOfDay() : null);
    }

    /**
     * With expire date in the given interval - time component is set to 23:59:59.999999.
     */
    public function withExpireDateIn(?DateInterval $interval): self
    {
        return $this->withExpireTime($interval ? Carbon::now()->add($interval)->endOfDay() : null);
    }

    /**
     * Get entities.
     *
     * @return array|\Illuminate\Database\Eloquent\Model[]
     */
    public function getEntities(): array
    {
        return Arr::get($this->options, 'entities', []);
    }

    /**
     * With entities.
     */
    public function withEntities(iterable|Model $entities = [], Model ...$remaining): self
    {
        Arr::set(
            $this->options,
            'entities',
            collect(is_iterable($entities) ? $entities : [$entities])->concat($remaining)->all()
        );

        return $this;
    }

    /**
     * Get owner.
     */
    public function getOwner(): ?Model
    {
        return Arr::get($this->options, 'owner', null);
    }

    /**
     * With owner.
     */
    public function withOwner(Model $owner): self
    {
        Arr::set($this->options, 'owner', $owner);

        return $this;
    }

    /**
     * Get model class name from config.
     */
    public static function model(string $name): ?string
    {
        return config('vouchers.models.' . $name);
    }

    /**
     * Get database table name for a model from config.
     */
    public static function table(string $name): ?string
    {
        return config('vouchers.tables.' . $name);
    }
}

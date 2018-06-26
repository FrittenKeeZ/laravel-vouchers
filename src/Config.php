<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use DateTime;
use DateInterval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Config
{
    /**
     * Dynamic options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Get dynamically set options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get code character list.
     *
     * @return string
     */
    public function getCharacters(): string
    {
        return array_get($this->options, 'characters', config('vouchers.characters'));
    }

    /**
     * With code character list.
     *
     * @param  string  $characters
     * @return $this
     */
    public function withCharacters(string $characters): self
    {
        array_set($this->options, 'characters', $characters);

        return $this;
    }

    /**
     * Get code mask.
     *
     * @return string
     */
    public function getMask(): string
    {
        return array_get($this->options, 'mask', config('vouchers.mask'));
    }

    /**
     * With code mask.
     *
     * @param  string  $mask
     * @return $this
     */
    public function withMask(string $mask): self
    {
        array_set($this->options, 'mask', $mask);

        return $this;
    }

    /**
     * Get code prefix.
     *
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return array_get($this->options, 'prefix', config('vouchers.prefix'));
    }

    /**
     * With code prefix.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function withPrefix(string $prefix): self
    {
        array_set($this->options, 'prefix', $prefix);

        return $this;
    }

    /**
     * Without code prefix.
     *
     * @return $this
     */
    public function withoutPrefix(): self
    {
        array_set($this->options, 'prefix', '');

        return $this;
    }

    /**
     * Get code suffix.
     *
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return array_get($this->options, 'suffix', config('vouchers.suffix'));
    }

    /**
     * With code suffix.
     *
     * @param  string  $suffix
     * @return $this
     */
    public function withSuffix(string $suffix): self
    {
        array_set($this->options, 'suffix', $suffix);

        return $this;
    }

    /**
     * Without code suffix.
     *
     * @return $this
     */
    public function withoutSuffix(): self
    {
        array_set($this->options, 'suffix', '');

        return $this;
    }

    /**
     * Get separator for prefix and suffix.
     *
     * @return string
     */
    public function getSeparator(): string
    {
        return array_get($this->options, 'separator', config('vouchers.separator'));
    }

    /**
     * With prefix and suffix separator.
     *
     * @param  string  $separator
     * @return $this
     */
    public function withSeparator(string $separator): self
    {
        array_set($this->options, 'separator', $separator);

        return $this;
    }

    /**
     * Without prefix and suffix separator.
     *
     * @return $this
     */
    public function withoutSeparator(): self
    {
        array_set($this->options, 'separator', '');

        return $this;
    }

    /**
     * Get metadata.
     *
     * @return array|null
     */
    public function getMetadata(): ?array
    {
        return array_get($this->options, 'metadata');
    }

    /**
     * With metdata.
     *
     * @param  array  $metadata
     * @return $this
     */
    public function withMetadata(array $metadata): self
    {
        array_set($this->options, 'metadata', $metadata);

        return $this;
    }

    /**
     * Get start time.
     *
     * @return \Carbon\Carbon|null
     */
    public function getStartTime(): ?Carbon
    {
        return array_get($this->options, 'starts_at');
    }

    /**
     * With start time.
     *
     * @param  \DateTime  $timestamp
     * @return $this
     */
    public function withStartTime(DateTime $timestamp): self
    {
        array_set($this->options, 'starts_at', Carbon::instance($timestamp));

        return $this;
    }

    /**
     * With start time in the given interval.
     *
     * @param  \DateInterval  $interval
     * @return $this
     */
    public function withStartTimeIn(DateInterval $interval): self
    {
        return $this->withStartTime(Carbon::now()->add($interval));
    }

    /**
     * With start date - time component is set to 00:00:00.000000.
     *
     * @param  \DateTime  $timestamp
     * @return $this
     */
    public function withStartDate(DateTime $timestamp): self
    {
        return $this->withStartTime(Carbon::instance($timestamp)->startOfDay());
    }

    /**
     * With start date in the given interval - time component is set to 00:00:00.000000.
     *
     * @param  \DateInterval  $interval
     * @return $this
     */
    public function withStartDateIn(DateInterval $interval): self
    {
        return $this->withStartTime(Carbon::now()->add($interval)->startOfDay());
    }

    /**
     * Get expire time.
     *
     * @return \Carbon\Carbon|null
     */
    public function getExpireTime(): ?Carbon
    {
        return array_get($this->options, 'expires_at');
    }

    /**
     * With expire time.
     *
     * @param  \DateTime  $timestamp
     * @return $this
     */
    public function withExpireTime(DateTime $timestamp): self
    {
        array_set($this->options, 'expires_at', Carbon::instance($timestamp));

        return $this;
    }

    /**
     * With expire time in the given interval.
     *
     * @param  \DateInterval  $interval
     * @return $this
     */
    public function withExpireTimeIn(DateInterval $interval): self
    {
        return $this->withExpireTime(Carbon::now()->add($interval));
    }

    /**
     * With expire date - time component is set to 00:00:00.000000.
     *
     * @param  \DateTime  $timestamp
     * @return $this
     */
    public function withExpireDate(DateTime $timestamp): self
    {
        return $this->withExpireTime(Carbon::instance($timestamp)->endOfDay());
    }

    /**
     * With expire date in the given interval - time component is set to 00:00:00.000000.
     *
     * @param  \DateInterval  $interval
     * @return $this
     */
    public function withExpireDateIn(DateInterval $interval): self
    {
        return $this->withExpireTime(Carbon::now()->add($interval)->endOfDay());
    }

    /**
     * Get entities.
     *
     * @return \Illuminate\Database\Eloquent\Model[]|array|null
     */
    public function getEntities(): ?array
    {
        return array_get($this->options, 'entities');
    }

    /**
     * With metdata.
     *
     * @param  \Illuminate\Database\Eloquent\Model  ...$entities
     * @return $this
     */
    public function withEntities(Model ...$entities): self
    {
        array_set($this->options, 'entities', $entities);

        return $this;
    }

    /**
     * Get model class name from config.
     *
     * @param  string  $name
     * @return string|null
     */
    public static function model(string $name): ?string
    {
        return config('vouchers.models.' . $name);
    }

    /**
     * Get database table name for a model from config.
     *
     * @param  string  $name
     * @return string|null
     */
    public static function table(string $name): ?string
    {
        return config('vouchers.tables.' . $name);
    }
}

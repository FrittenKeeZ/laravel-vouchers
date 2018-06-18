<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

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
     * Get code mask.
     *
     * @return string
     */
    public function getMask(): string
    {
        return array_get($this->options, 'mask', config('vouchers.mask'));
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
     * Get code suffix.
     *
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return array_get($this->options, 'suffix', config('vouchers.suffix'));
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

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use DateTimeZone;

class DomainConfig
{
    public const string TYPE_B2C = 'b2c';
    public const string TYPE_B2B = 'b2b';

    /**
     * @param int $id
     * @param string $url
     * @param string $name
     * @param string $locale
     * @param \DateTimeZone $dateTimeZone
     * @param string $baseUrl
     * @param string $type
     * @param bool $loadDemoData
     * @param string|null $postfix
     */
    public function __construct(
        protected int $id,
        protected string $url,
        protected string $name,
        protected string $locale,
        protected DateTimeZone $dateTimeZone,
        protected readonly string $baseUrl,
        protected string $type = self::TYPE_B2C,
        protected readonly bool $loadDemoData = true,
        protected readonly ?string $postfix = null,
    ) {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return bool
     */
    public function isHttps(): bool
    {
        return str_starts_with($this->url, 'https://');
    }

    /**
     * @return \DateTimeZone
     */
    public function getDateTimeZone(): DateTimeZone
    {
        return $this->dateTimeZone;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isB2b(): bool
    {
        return $this->type === self::TYPE_B2B;
    }

    /**
     * @return bool
     */
    public function isAllowedInDataFixtures(): bool
    {
        return $this->loadDemoData;
    }

    /**
     * @return string|null
     */
    public function getPostfix(): ?string
    {
        return $this->postfix;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}

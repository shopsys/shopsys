<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Domain\Config\Exception\NoCurrenciesConfiguredForDomainException;

class DomainConfig
{
    public const string TYPE_B2C = 'b2c';
    public const string TYPE_B2B = 'b2b';

    /**
     * @param string[] $currencyCodes
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
        protected readonly array $currencyCodes = [],
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function isHttps(): bool
    {
        return str_starts_with($this->url, 'https://');
    }

    public function getDateTimeZone(): DateTimeZone
    {
        return $this->dateTimeZone;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isB2b(): bool
    {
        return $this->type === self::TYPE_B2B;
    }

    public function isAllowedInDataFixtures(): bool
    {
        return $this->loadDemoData;
    }

    public function getPostfix(): ?string
    {
        return $this->postfix;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return string[]
     */
    public function getCurrencyCodes(): array
    {
        return $this->currencyCodes;
    }

    public function getDefaultCurrencyCode(): string
    {
        if ($this->currencyCodes === []) {
            throw new NoCurrenciesConfiguredForDomainException($this->id);
        }

        return $this->currencyCodes[0];
    }

    public function hasCurrencyCode(string $currencyCode): bool
    {
        return in_array($currencyCode, $this->currencyCodes, true);
    }
}

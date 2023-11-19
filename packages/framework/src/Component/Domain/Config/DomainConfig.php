<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use DateTimeZone;

class DomainConfig
{
    public const STYLES_DIRECTORY_DEFAULT = 'common';

    /**
     * @param int $id
     * @param string $url
     * @param string $name
     * @param string $locale
     * @param \DateTimeZone $dateTimeZone
     * @param string $stylesDirectory
     * @param string|null $designId
     */
    public function __construct(
        protected int $id,
        protected string $url,
        protected string $name,
        protected string $locale,
        protected DateTimeZone $dateTimeZone,
        protected string $stylesDirectory = self::STYLES_DIRECTORY_DEFAULT,
        protected ?string $designId = null,
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
     * @return string
     */
    public function getStylesDirectory(): string
    {
        return $this->stylesDirectory;
    }

    /**
     * @return string|null
     */
    public function getDesignId(): ?string
    {
        return $this->designId;
    }

    /**
     * @return bool
     */
    public function isHttps(): bool
    {
        return strpos($this->url, 'https://') === 0;
    }

    /**
     * @return \DateTimeZone
     */
    public function getDateTimeZone(): DateTimeZone
    {
        return $this->dateTimeZone;
    }
}

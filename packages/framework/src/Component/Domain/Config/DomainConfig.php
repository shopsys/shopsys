<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

class DomainConfig
{
    public const STYLES_DIRECTORY_DEFAULT = 'common';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $stylesDirectory;

    /**
     * @var string|null
     */
    protected $designId;

    /**
     * @param int $id
     * @param string $url
     * @param string $name
     * @param string $locale
     * @param string $stylesDirectory
     * @param string|null $designId
     */
    public function __construct(int $id, string $url, string $name, string $locale, string $stylesDirectory = self::STYLES_DIRECTORY_DEFAULT, ?string $designId = null)
    {
        $this->id = $id;
        $this->url = $url;
        $this->name = $name;
        $this->locale = $locale;
        $this->stylesDirectory = $stylesDirectory;
        $this->designId = $designId;
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
}

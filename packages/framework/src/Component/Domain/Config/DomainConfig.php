<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

class DomainConfig
{
    const STYLES_DIRECTORY_DEFAULT = 'common';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $stylesDirectory;
    
    public function __construct(int $id, string $url, string $name, string $locale, string $stylesDirectory = self::STYLES_DIRECTORY_DEFAULT)
    {
        $this->id = $id;
        $this->url = $url;
        $this->name = $name;
        $this->locale = $locale;
        $this->stylesDirectory = $stylesDirectory;
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

    public function getStylesDirectory(): string
    {
        return $this->stylesDirectory;
    }

    public function isHttps(): bool
    {
        return strpos($this->url, 'https://') === 0;
    }
}

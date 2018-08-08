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

    /**
     * @param int $id
     * @param string $url
     * @param string $name
     * @param string $locale
     * @param string $stylesDirectory
     */
    public function __construct($id, $url, $name, $locale, $stylesDirectory = self::STYLES_DIRECTORY_DEFAULT)
    {
        $this->id = $id;
        $this->url = $url;
        $this->name = $name;
        $this->locale = $locale;
        $this->stylesDirectory = $stylesDirectory;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getStylesDirectory()
    {
        return $this->stylesDirectory;
    }

    public function isHttps()
    {
        return strpos($this->url, 'https://') === 0;
    }
}

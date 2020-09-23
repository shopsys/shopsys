<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Brand;

class BrandView
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $mainUrl;

    /**
     * @param int $id
     * @param string $name
     * @param string $mainUrl
     */
    public function __construct(int $id, string $name, string $mainUrl)
    {
        $this->id = $id;
        $this->name = $name;
        $this->mainUrl = $mainUrl;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMainUrl(): string
    {
        return $this->mainUrl;
    }
}

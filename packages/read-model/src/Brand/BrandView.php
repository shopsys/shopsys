<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Brand;

class BrandView
{
    /**
     * @param int $id
     * @param string $name
     * @param string $mainUrl
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $mainUrl,
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

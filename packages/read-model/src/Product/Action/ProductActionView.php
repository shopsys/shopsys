<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Action;

class ProductActionView
{
    /**
     * @param int $id
     * @param bool $sellingDenied
     * @param bool $isMainVariant
     * @param string $detailUrl
     */
    public function __construct(protected readonly int $id, protected readonly bool $sellingDenied, protected readonly bool $isMainVariant, protected readonly string $detailUrl)
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isSellingDenied(): bool
    {
        return $this->sellingDenied;
    }

    /**
     * @return bool
     */
    public function isMainVariant(): bool
    {
        return $this->isMainVariant;
    }

    /**
     * @return string
     */
    public function getDetailUrl(): string
    {
        return $this->detailUrl;
    }
}

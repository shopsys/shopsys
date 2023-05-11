<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Action;

class ProductActionView
{
    protected int $id;

    protected bool $sellingDenied;

    protected bool $isMainVariant;

    protected string $detailUrl;

    /**
     * @param int $id
     * @param bool $sellingDenied
     * @param bool $isMainVariant
     * @param string $detailUrl
     */
    public function __construct(int $id, bool $sellingDenied, bool $isMainVariant, string $detailUrl)
    {
        $this->id = $id;
        $this->sellingDenied = $sellingDenied;
        $this->isMainVariant = $isMainVariant;
        $this->detailUrl = $detailUrl;
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

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\View;

/**
 * @experimental
 *
 * Class representing products actions in lists in FE templates (to avoid usage of Doctrine entities a hence achieve performance gain)
 * @see \Shopsys\FrameworkBundle\Model\Product\View\ListedProductView
 */
class ProductActionView
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $sellingDenied;

    /**
     * @var bool
     */
    protected $mainVariant;

    /**
     * @var string
     */
    protected $detailUrl;

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
        return $this->mainVariant;
    }

    /**
     * @return string
     */
    public function getDetailUrl(): string
    {
        return $this->detailUrl;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param bool $sellingDenied
     */
    public function setSellingDenied(bool $sellingDenied): void
    {
        $this->sellingDenied = $sellingDenied;
    }

    /**
     * @param bool $mainVariant
     */
    public function setMainVariant(bool $mainVariant): void
    {
        $this->mainVariant = $mainVariant;
    }

    /**
     * @param string $detailUrl
     */
    public function setDetailUrl(string $detailUrl): void
    {
        $this->detailUrl = $detailUrl;
    }
}

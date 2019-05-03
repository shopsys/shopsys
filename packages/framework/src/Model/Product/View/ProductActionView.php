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
     * @param int $id
     * @param bool $sellingDenied
     * @param bool $mainVariant
     * @param string $detailUrl
     */
    public function __construct(int $id, bool $sellingDenied, bool $mainVariant, string $detailUrl)
    {
        $this->id = $id;
        $this->sellingDenied = $sellingDenied;
        $this->mainVariant = $mainVariant;
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
        return $this->mainVariant;
    }

    /**
     * @return string
     */
    public function getDetailUrl(): string
    {
        return $this->detailUrl;
    }
}

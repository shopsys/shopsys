<?php

declare(strict_types=1);

namespace App\Model\Product\Action;

use Shopsys\ReadModelBundle\Product\Action\ProductActionView as BaseProductActionView;

class ProductActionView extends BaseProductActionView
{
    /**
     * @var int|null
     */
    protected ?int $stockQuantity;

    /**
     * @var bool
     */
    protected bool $productAvailable;

    /**
     * @var bool
     */
    protected bool $hasPreorder;

    /**
     * @param int $id
     * @param bool $sellingDenied
     * @param bool $isMainVariant
     * @param string $detailUrl
     * @param int|null $stockQuantity
     * @param bool $productAvailable
     * @param bool $hasPreorder
     */
    public function __construct(
        int $id,
        bool $sellingDenied,
        bool $isMainVariant,
        string $detailUrl,
        ?int $stockQuantity,
        bool $productAvailable,
        bool $hasPreorder
    ) {
        parent::__construct($id, $sellingDenied, $isMainVariant, $detailUrl);

        $this->stockQuantity = $stockQuantity;
        $this->productAvailable = $productAvailable;
        $this->hasPreorder = $hasPreorder;
    }

    /**
     * @return bool
     */
    public function isProductAvailable(): bool
    {
        return $this->productAvailable;
    }

    /**
     * @return int|null
     */
    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    /**
     * @return int
     */
    public function getMaximumOrderQuantity(): int
    {
        if ($this->hasPreorder()) {
            return PHP_INT_MAX;
        }

        return $this->getStockQuantity() ?? 0;
    }

    /**
     * @return bool
     */
    public function hasPreorder(): bool
    {
        return $this->hasPreorder;
    }

    /**
     * @param int $stockQuantity
     */
    public function setStockQuantity(int $stockQuantity): void
    {
        $this->stockQuantity = $stockQuantity;
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Product\Action;

use Shopsys\ReadModelBundle\Product\Action\ProductActionView as BaseProductActionView;

class ProductActionView extends BaseProductActionView
{
    /**
     * @var int|null
     */
    protected $stockQuantity;

    /**
     * @param int $id
     * @param bool $sellingDenied
     * @param bool $isMainVariant
     * @param string $detailUrl
     * @param int|null $stockQuantity
     */
    public function __construct(int $id, bool $sellingDenied, bool $isMainVariant, string $detailUrl, ?int $stockQuantity)
    {
        parent::__construct($id, $sellingDenied, $isMainVariant, $detailUrl);
        $this->stockQuantity = $stockQuantity;
    }

    /**
     * @return int|null
     */
    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    /**
     * @param int $stockQuantity
     */
    public function setStockQuantity(int $stockQuantity): void
    {
        $this->stockQuantity = $stockQuantity;
    }
}

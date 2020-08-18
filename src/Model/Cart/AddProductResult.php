<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\AddProductResult as BaseAddProductResult;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

/**
 * @property \App\Model\Cart\Item\CartItem $cartItem
 * @method \App\Model\Cart\Item\CartItem getCartItem()
 */
class AddProductResult extends BaseAddProductResult
{
    /**
     * @var int
     */
    protected $notOnStockQuantity;

    /**
     * @var int|null
     */
    private $overLimitQuantity;

    /**
     * @var bool
     */
    private $isQuantityOverLimit;

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     * @param mixed $isNew
     * @param mixed $addedQuantity
     * @param mixed $notOnStockQuantity
     * @param mixed $overLimitQuantity
     * @param mixed $isQuantityOverLimit
     */
    public function __construct(
        CartItem $cartItem,
        $isNew,
        $addedQuantity,
        $notOnStockQuantity,
        $overLimitQuantity,
        $isQuantityOverLimit
    ) {
        parent::__construct($cartItem, $isNew, $addedQuantity);
        $this->notOnStockQuantity = $notOnStockQuantity;
        $this->overLimitQuantity = $overLimitQuantity;
        $this->isQuantityOverLimit = $isQuantityOverLimit;
    }

    /**
     * @return int
     */
    public function getNotOnStockQuantity(): int
    {
        return $this->notOnStockQuantity;
    }

    /**
     * @return int|null
     */
    public function getOverLimitQuantity(): ?int
    {
        return $this->overLimitQuantity;
    }

    /**
     * @return bool
     */
    public function isQuantityOverLimit(): bool
    {
        return $this->isQuantityOverLimit;
    }
}

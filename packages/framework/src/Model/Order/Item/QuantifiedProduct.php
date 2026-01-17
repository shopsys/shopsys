<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Product\Product;

class QuantifiedProduct
{
    public const string CART_ITEM_TYPE_KEY = 'cartItemType';

    protected int $quantity;

    /**
     * @var array<string,mixed>
     */
    protected array $additionalData;

    /**
     * @param int $quantity
     */
    public function __construct(protected readonly Product $product, $quantity)
    {
        $this->quantity = $quantity;
        $this->additionalData = [];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getAdditionalData(string $key): mixed
    {
        return $this->additionalData[$key] ?? null;
    }

    public function setAdditionalData(string $key, mixed $additionalData): void
    {
        $this->additionalData[$key] = $additionalData;
    }
}

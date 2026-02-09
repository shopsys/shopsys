<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Product\Product;

class QuantifiedProduct
{
    public const string CART_ITEM_TYPE_KEY = 'cartItemType';

    /**
     * @var array<string,mixed>
     */
    protected array $additionalData;

    public function __construct(protected readonly Product $product, protected int $quantity)
    {
        $this->additionalData = [];
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getQuantity(): int
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

<?php

declare(strict_types=1);

namespace App\Model\Cart\Splitting;

use App\Model\Product\Type\ProductType;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview;

class CartView
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview
     */
    private $orderPreview;

    /**
     * @var \App\Model\Product\Type\ProductType
     */
    private $productType;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param \App\Model\Product\Type\ProductType $productType
     */
    public function __construct(OrderPreview $orderPreview, ProductType $productType)
    {
        $this->orderPreview = $orderPreview;
        $this->productType = $productType;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview
     */
    public function getOrderPreview(): OrderPreview
    {
        return $this->orderPreview;
    }

    /**
     * @return \App\Model\Product\Type\ProductType
     */
    public function getProductType(): ProductType
    {
        return $this->productType;
    }
}

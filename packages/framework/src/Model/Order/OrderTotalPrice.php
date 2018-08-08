<?php

namespace Shopsys\FrameworkBundle\Model\Order;

class OrderTotalPrice
{
    /**
     * @var string
     */
    private $priceWithVat;

    /**
     * @var string
     */
    private $priceWithoutVat;

    /**
     * @var string
     */
    private $productPriceWithVat;

    /**
     * @param string $priceWithVat
     * @param string $priceWithoutVat
     * @param string $productPriceWithVat
     */
    public function __construct($priceWithVat, $priceWithoutVat, $productPriceWithVat)
    {
        $this->priceWithVat = $priceWithVat;
        $this->priceWithoutVat = $priceWithoutVat;
        $this->productPriceWithVat = $productPriceWithVat;
    }

    public function getPriceWithVat(): string
    {
        return $this->priceWithVat;
    }

    public function getPriceWithoutVat(): string
    {
        return $this->priceWithoutVat;
    }

    public function getProductPriceWithVat(): string
    {
        return $this->productPriceWithVat;
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Product\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="promo_code_products")
 * @ORM\Entity
 */
class PromoCodeProduct
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="App\Model\Order\PromoCode\PromoCode")
     * @ORM\JoinColumn(nullable=false, name="promo_code_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $promoCode;

    /**
     * @var \App\Model\Product\Product
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $product;

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Product\Product $product
     */
    public function __construct(PromoCode $promoCode, Product $product)
    {
        $this->promoCode = $promoCode;
        $this->product = $product;
    }

    /**
     * @return \App\Model\Product\Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }
}

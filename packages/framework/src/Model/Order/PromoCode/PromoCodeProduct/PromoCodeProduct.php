<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Product\Product;

#[ORM\Table(name: 'promo_code_products')]
#[ORM\Entity]
class PromoCodeProduct
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    #[ORM\JoinColumn(nullable: false, name: 'promo_code_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: PromoCode::class)]
    #[ORM\Id]
    protected $promoCode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[ORM\JoinColumn(nullable: false, name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\Id]
    protected $product;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function __construct(PromoCode $promoCode, Product $product)
    {
        $this->promoCode = $promoCode;
        $this->product = $product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'product_promotion_xy')]
#[ORM\Entity]
class ProductPromotionXy
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false, name: 'buy_quantity')]
    protected $buyQuantity;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false, name: 'free_quantity')]
    protected $freeQuantity;

    public function __construct(ProductPromotionXyData $productPromotionXyData)
    {
        $this->setData($productPromotionXyData);
    }

    /**
     * @return int
     */
    public function getBuyQuantity()
    {
        return $this->buyQuantity;
    }

    /**
     * @return int
     */
    public function getFreeQuantity()
    {
        return $this->freeQuantity;
    }

    public function setData(ProductPromotionXyData $productPromotionXyData): void
    {
        $this->buyQuantity = $productPromotionXyData->buyQuantity;
        $this->freeQuantity = $productPromotionXyData->freeQuantity;
    }
}

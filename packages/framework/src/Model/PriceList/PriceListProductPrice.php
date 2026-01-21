<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product;

#[ORM\Table(name: 'price_list_product_prices')]
#[ORM\Entity]
class PriceListProductPrice
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[ORM\Column(type: 'money', precision: 20, scale: 6, nullable: false)]
    protected $priceAmount;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PriceList\PriceList
     */
    #[ORM\JoinColumn(name: 'price_list_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: PriceList::class, inversedBy: 'priceListProductPrices')]
    protected $priceList;

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData $priceListProductPriceData
     */
    public function __construct(
        PriceListProductPriceData $priceListProductPriceData,
    ) {
        $this->product = $priceListProductPriceData->product;
        $this->priceAmount = $priceListProductPriceData->priceAmount;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceAmount()
    {
        return $this->priceAmount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceList $priceList
     */
    public function setPriceList($priceList)
    {
        $this->priceList = $priceList;
    }
}

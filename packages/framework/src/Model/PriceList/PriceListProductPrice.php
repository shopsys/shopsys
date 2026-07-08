<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'price_list_product_prices')]
#[ORM\Entity]
class PriceListProductPrice
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6, nullable: false)]
    protected $priceAmount;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PriceList\PriceList
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'price_list_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: PriceList::class, inversedBy: 'priceListProductPrices')]
    protected $priceList;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'currency_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Currency::class)]
    protected $currency;

    public function __construct(
        PriceListProductPriceData $priceListProductPriceData,
    ) {
        $this->product = $priceListProductPriceData->product;
        $this->priceAmount = $priceListProductPriceData->priceAmount;
        $this->currency = $priceListProductPriceData->currency;
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
    public function setPriceList($priceList): void
    {
        $this->priceList = $priceList;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}

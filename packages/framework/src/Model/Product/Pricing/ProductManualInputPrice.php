<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'product_manual_input_prices')]
#[ORM\Entity]
class ProductManualInputPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'product_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'pricing_group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: PricingGroup::class)]
    protected $pricingGroup;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'currency_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Currency::class)]
    protected $currency;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6, nullable: true)]
    protected $inputPrice;

    public function __construct(Product $product, PricingGroup $pricingGroup, Currency $currency, ?Money $inputPrice)
    {
        $this->product = $product;
        $this->pricingGroup = $pricingGroup;
        $this->currency = $currency;
        $this->setInputPrice($inputPrice);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup()
    {
        return $this->pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getInputPrice()
    {
        return $this->inputPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $inputPrice
     */
    public function setInputPrice($inputPrice): void
    {
        $this->inputPrice = $inputPrice;
    }
}

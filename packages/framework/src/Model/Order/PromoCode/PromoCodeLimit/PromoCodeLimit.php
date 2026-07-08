<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Entity]
class PromoCodeLimit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    #[ORM\ManyToOne(targetEntity: PromoCode::class)]
    #[ORM\Id]
    protected $promoCode;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'decimal', precision: 20, scale: 6)]
    #[ORM\Id]
    protected $fromPrice;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'decimal', precision: 20, scale: 6)]
    protected $discount;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'currency_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Currency::class)]
    protected $currency;

    public function __construct(string $from, string $discount, Currency $currency)
    {
        $this->fromPrice = $from;
        $this->discount = $discount;
        $this->currency = $currency;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getFromPrice()
    {
        return $this->fromPrice;
    }

    /**
     * @return string
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function setPromoCode($promoCode): void
    {
        $this->promoCode = $promoCode;
    }
}

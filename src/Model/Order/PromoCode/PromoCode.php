<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;

/**
 * @ORM\Table(name="promo_codes",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="domain_code_unique", columns={
 *         "domain_id", "code"
 *     })}
 * )
 * @ORM\Entity
 */
class PromoCode extends BasePromoCode
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     *
     * @ORM\Column(type="text",unique=false)
     */
    protected $code;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    public function __construct(PromoCodeData $promoCodeData)
    {
        parent::__construct($promoCodeData);
        $this->domainId = $promoCodeData->domainId;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    public function edit(PromoCodeData $promoCodeData): void
    {
        parent::edit($promoCodeData);
        $this->domainId = $promoCodeData->domainId;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }
}

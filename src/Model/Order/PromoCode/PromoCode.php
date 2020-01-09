<?php

declare(strict_types=1);


namespace App\Model\Order\PromoCode;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;

/**
 * @ORM\Table(name="promo_codes")
 * @ORM\Entity
 */
class PromoCode extends BasePromoCode
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    public function __construct(PromoCodeData $promoCodeData){
        parent::__construct($promoCodeData);
        $this->setDomainId($promoCodeData->domainId);
    }

    /**
     * @return int
     */
    public function getDomainId(): int{
        return $this->domainId;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId(int $domainId): void{
        $this->domainId = $domainId;
    }

}
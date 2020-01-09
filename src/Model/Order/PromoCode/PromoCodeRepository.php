<?php

declare(strict_types=1);


namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository as BasePromoCodeRepository;

class PromoCodeRepository extends BasePromoCodeRepository
{

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPromoCodeRepository(){
        return $this->em->getRepository(PromoCode::class);
    }

}
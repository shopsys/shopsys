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

    /**
     * @param $code
     * @param $domainId
     * @return PromoCode|null
     */
    public function findByCodeAndDomainId($code, $domainId)
    {
        d([$code,$domainId]);
        $result = $this->getPromoCodeRepository()->findOneBy(['code' => $code,'domainId'=>$domainId]);
        d($result);

        return $result;
    }

}
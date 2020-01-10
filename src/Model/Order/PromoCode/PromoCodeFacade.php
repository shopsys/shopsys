<?php

declare(strict_types=1);


namespace App\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade as BasePromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;

class PromoCodeFacade extends BasePromoCodeFacade
{
    /**
     * @var Domain
     */
    private $domain;

    /**
     * PromoCodeFacade constructor.
     * @param EntityManagerInterface $em
     * @param PromoCodeRepository $promoCodeRepository
     * @param PromoCodeFactoryInterface $promoCodeFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeFactoryInterface $promoCodeFactory,
        Domain $domain
    ){
        parent::__construct($em, $promoCodeRepository, $promoCodeFactory);
        $this->domain = $domain;
    }

    public function findPromoCodeByCode($code){

        return $this->promoCodeRepository->findByCodeAndDomainId($code, $this->domain->getId());
    }


}
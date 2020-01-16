<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade as BasePromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
 * @method \App\Model\Order\PromoCode\PromoCode create(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method \App\Model\Order\PromoCode\PromoCode edit(int $promoCodeId, \App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 */
class PromoCodeFacade extends BasePromoCodeFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface $promoCodeFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        EntityManagerInterface $em,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeFactoryInterface $promoCodeFactory,
        Domain $domain
    ) {
        parent::__construct($em, $promoCodeRepository, $promoCodeFactory);
        $this->domain = $domain;
    }

    /**
     * @param mixed $code
     */
    public function findPromoCodeByCode($code)
    {
        return $this->promoCodeRepository->findByCodeAndDomainId($code, $this->domain->getId());
    }
}

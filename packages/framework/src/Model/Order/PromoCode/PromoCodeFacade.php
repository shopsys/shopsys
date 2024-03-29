<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;

class PromoCodeFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface $promoCodeFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PromoCodeRepository $promoCodeRepository,
        protected readonly PromoCodeFactoryInterface $promoCodeFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function create(PromoCodeData $promoCodeData)
    {
        $promoCode = $this->promoCodeFactory->create($promoCodeData);
        $this->em->persist($promoCode);
        $this->em->flush();

        return $promoCode;
    }

    /**
     * @param int $promoCodeId
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function edit($promoCodeId, PromoCodeData $promoCodeData)
    {
        $promoCode = $this->getById($promoCodeId);
        $promoCode->edit($promoCodeData);
        $this->em->flush();

        return $promoCode;
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function getById($promoCodeId)
    {
        return $this->promoCodeRepository->getById($promoCodeId);
    }

    /**
     * @param int $promoCodeId
     */
    public function deleteById($promoCodeId)
    {
        $promoCode = $this->getById($promoCodeId);
        $this->em->remove($promoCode);
        $this->em->flush();
    }

    /**
     * @param string $code
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function findPromoCodeByCode($code)
    {
        return $this->promoCodeRepository->findByCode($code);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getAll()
    {
        return $this->promoCodeRepository->getAll();
    }
}

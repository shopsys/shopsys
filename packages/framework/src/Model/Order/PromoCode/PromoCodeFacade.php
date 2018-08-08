<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;

class PromoCodeFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository
     */
    protected $promoCodeRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface
     */
    protected $promoCodeFactory;

    public function __construct(
        EntityManagerInterface $em,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeFactoryInterface $promoCodeFactory
    ) {
        $this->em = $em;
        $this->promoCodeRepository = $promoCodeRepository;
        $this->promoCodeFactory = $promoCodeFactory;
    }

    public function create(PromoCodeData $promoCodeData): \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        $promoCode = $this->promoCodeFactory->create($promoCodeData);
        $this->em->persist($promoCode);
        $this->em->flush();

        return $promoCode;
    }

    public function edit(int $promoCodeId, PromoCodeData $promoCodeData): \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        $promoCode = $this->getById($promoCodeId);
        $promoCode->edit($promoCodeData);
        $this->em->flush();

        return $promoCode;
    }

    public function getById(int $promoCodeId): \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        return $this->promoCodeRepository->getById($promoCodeId);
    }

    public function deleteById(int $promoCodeId): void
    {
        $promoCode = $this->getById($promoCodeId);
        $this->em->remove($promoCode);
        $this->em->flush();
    }

    public function findPromoCodeByCode(string $code): ?\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        return $this->promoCodeRepository->findByCode($code);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getAll(): array
    {
        return $this->promoCodeRepository->getAll();
    }
}

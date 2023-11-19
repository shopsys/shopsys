<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException;

class PromoCodeRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPromoCodeRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(PromoCode::class);
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function findById($promoCodeId): ?\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        return $this->getPromoCodeRepository()->find($promoCodeId);
    }

    /**
     * @param string $code
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function findByCode($code): ?\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        return $this->getPromoCodeRepository()->findOneBy(['code' => $code]);
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function getById($promoCodeId): \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        $promoCode = $this->findById($promoCodeId);

        if ($promoCode === null) {
            throw new PromoCodeNotFoundException(
                'Promo code with ID ' . $promoCodeId . ' not found.',
            );
        }

        return $promoCode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getAll(): array
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('pc')
            ->from(PromoCode::class, 'pc');

        return $queryBuilder->getQuery()->execute();
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException;

class PromoCodeRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPromoCodeRepository(): EntityRepository
    {
        return $this->em->getRepository(PromoCode::class);
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function findById(int $promoCodeId): ?PromoCode
    {
        return $this->getPromoCodeRepository()->find($promoCodeId);
    }

    /**
     * @param string $code
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function findByCode(string $code): ?PromoCode
    {
        return $this->getPromoCodeRepository()->findOneBy(['code' => $code]);
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function getById(int $promoCodeId): PromoCode
    {
        $promoCode = $this->findById($promoCodeId);

        if ($promoCode === null) {
            throw new PromoCodeNotFoundException(
                'Promo code with ID ' . $promoCodeId . ' not found.'
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

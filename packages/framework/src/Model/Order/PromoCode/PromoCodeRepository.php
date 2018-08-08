<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;

class PromoCodeRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getPromoCodeRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(PromoCode::class);
    }
    
    public function findById(int $promoCodeId): ?\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        return $this->getPromoCodeRepository()->find($promoCodeId);
    }
    
    public function findByCode(string $code): ?\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        return $this->getPromoCodeRepository()->findOneBy(['code' => $code]);
    }
    
    public function getById(int $promoCodeId): \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        $promoCode = $this->findById($promoCodeId);

        if ($promoCode === null) {
            throw new \Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException(
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

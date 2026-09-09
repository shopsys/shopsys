<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeNotFoundException;

class PromoCodeRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getPromoCodeRepository(): EntityRepository
    {
        return $this->em->getRepository(PromoCode::class);
    }

    public function findById(int $promoCodeId): ?PromoCode
    {
        return $this->getPromoCodeRepository()->find($promoCodeId);
    }

    public function findByCodeAndDomainId(string $code, int $domainId): ?PromoCode
    {
        return $this->getPromoCodeRepository()->findOneBy([
            'code' => $code,
            'domainId' => $domainId,
        ]);
    }

    public function getById(int $promoCodeId): PromoCode
    {
        $promoCode = $this->findById($promoCodeId);

        if ($promoCode === null) {
            throw new PromoCodeNotFoundException(
                'Promo code with ID ' . $promoCodeId . ' not found.',
            );
        }

        return $promoCode;
    }

    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getPromoCodeRepository()
            ->createQueryBuilder('pc');
    }

    /**
     * @return string[]
     */
    public function getPromoCodeCodes(?int $filterByBatchId = null): array
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->select('pc.code');

        if ($filterByBatchId !== null) {
            $queryBuilder->andWhere('pc.massGenerateBatchId = :batchId')
                ->setParameter('batchId', $filterByBatchId);
        }

        return array_column($queryBuilder->getQuery()->getResult(), 'code');
    }

    public function getMassLastGeneratedBatchId(): int
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->select('COALESCE(MAX(pc.massGenerateBatchId), 0) AS lastBatchId');

        $result = $queryBuilder->getQuery()->getSingleResult();

        return $result['lastBatchId'];
    }
}

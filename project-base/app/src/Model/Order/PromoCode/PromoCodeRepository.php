<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository as BasePromoCodeRepository;

/**
 * @method \App\Model\Order\PromoCode\PromoCode|null findById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 * @method \App\Model\Order\PromoCode\PromoCode|null findByCodeAndDomainId(string $code, int $domainId)
 */
class PromoCodeRepository extends BasePromoCodeRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getPromoCodeRepository()
            ->createQueryBuilder('pc');
    }

    /**
     * @return string[]
     */
    public function getAllPromoCodeCodes(): array
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->select('pc.code');

        return array_column($queryBuilder->getQuery()->execute(), 'code');
    }

    /**
     * @return int
     */
    public function getMassLastGeneratedBatchId(): int
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->select('COALESCE(MAX(pc.massGenerateBatchId), 0) AS lastBatchId');

        $result = $queryBuilder->getQuery()->getSingleResult();

        return $result['lastBatchId'];
    }

    /**
     * @param int $batchId
     * @return \App\Model\Order\PromoCode\PromoCode[]|null
     */
    public function findByMassBatchId(int $batchId): ?array
    {
        return $this->getPromoCodeRepository()->findBy(['massGenerateBatchId' => $batchId]);
    }
}

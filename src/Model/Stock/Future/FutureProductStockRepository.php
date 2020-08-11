<?php

declare(strict_types=1);

namespace App\Model\Stock\Future;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FutureProductStockRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

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
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(FutureProductStock::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('fps')
            ->from(FutureProductStock::class, 'fps');
    }

    /**
     * @param string $erpId
     * @return \App\Model\Stock\Future\FutureProductStock
     */
    public function getByErpId(string $erpId): FutureProductStock
    {
        $futureProductStock = $this->findByErpId($erpId);
        if ($futureProductStock === null) {
            throw new NotFoundHttpException();
        }

        return$futureProductStock;
    }

    /**
     * @param string $erpId
     * @return \App\Model\Stock\Future\FutureProductStock|null
     */
    public function findByErpId(string $erpId): ?FutureProductStock
    {
        return $this->getRepository()->findOneBy(['erpId' => $erpId]);
    }

    /**
     * @param string $sku
     * @param string $storeCode
     * @return \App\Model\Stock\Future\FutureProductStock|null
     */
    public function findClosestFutureProductStockBySkuAndStoreCode(string $sku, string $storeCode): ?FutureProductStock
    {
        $result = $this->getQueryBuilder()
            ->select('fps as item')
            ->addSelect('COALESCE( fps.dateConfirmedArrival, fps.dateExpectedArrival ) as tmpDate')
            ->where('fps.sku = :sku')
            ->andWhere('fps.storeCode = :storeCode')
            ->andWhere('fps.isLate = FALSE')
            ->andWhere('COALESCE( fps.dateConfirmedArrival, fps.dateExpectedArrival ) >= current_date()')
            ->orderBy('tmpDate')
            ->addOrderBy('fps.amount', 'DESC')
            ->setParameters(['sku' => $sku, 'storeCode' => $storeCode])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['item'] ?? null;
    }

    public function cleanOrdersAfterDeadline(): void
    {
        $nativeQuery = $this->em->createNativeQuery(
            'DELETE FROM future_product_stocks WHERE is_late = TRUE OR COALESCE(date_confirmed_arrival, date_expected_arrival) < current_date',
            new ResultSetMapping()
        );

        $nativeQuery->execute();
    }
}

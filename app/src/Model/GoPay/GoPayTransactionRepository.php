<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\Order\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use GoPay\Definition\Response\PaymentStatus;

class GoPayTransactionRepository
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
    public function getGoPayTransactionRepository(): EntityRepository
    {
        return $this->em->getRepository(GoPayTransaction::class);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \App\Model\GoPay\GoPayTransaction[]
     */
    public function findAllByOrder(Order $order): array
    {
        return $this->getGoPayTransactionRepository()->findBy(['order' => $order]);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return bool
     */
    public function isOrderPaid(Order $order): bool
    {
        $queryBuilder = $this->getAllQueryBuilder();

        $queryBuilder
            ->select('COUNT(gpt)')
            ->where('gpt.order = :order')
            ->andWhere('gpt.goPayStatus = :status')
            ->setParameter('status', PaymentStatus::PAID)
            ->setParameter('order', $order);

        return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('gpt')
            ->from(GoPayTransaction::class, 'gpt');
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \App\Model\GoPay\GoPayTransaction|null
     */
    public function getLastTransactionByOrder(Order $order): ?GoPayTransaction
    {
        $queryBuilder = $this->getAllQueryBuilder();

        $queryBuilder
            ->where('gpt.order = :order')
            ->orderBy('gpt.goPayId', 'DESC')
            ->setParameter('order', $order)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}

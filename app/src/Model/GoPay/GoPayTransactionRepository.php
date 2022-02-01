<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\Order\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('gpt')
            ->from(GoPayTransaction::class, 'gpt');
    }
}

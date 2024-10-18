<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;

class OrderRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getOrderRepository()
    {
        return $this->em->getRepository(Order::class);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findLastOrderByEmailAndDomainId(string $email, int $domainId): ?Order
    {
        return $this->getOrderRepository()->findOneBy([
            'email' => $email,
            'domainId' => $domainId,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;

class TransportPriceRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param int $cartTotalWeight
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice
     */
    public function getTransportPriceOnDomainByTransportAndClosestWeight(
        int $domainId,
        Transport $transport,
        int $cartTotalWeight,
    ): TransportPrice {
        $transportPrice = $this->em->createQueryBuilder()
            ->select('tp')
            ->from(TransportPrice::class, 'tp')
            ->where('tp.transport = :transport')
            ->andWhere('tp.domainId = :domainId')
            ->andWhere('((tp.maxWeight >= :cartTotalWeight) OR (tp.maxWeight IS NULL))')
            ->setParameter('transport', $transport)
            ->setParameter('domainId', $domainId)
            ->setParameter('cartTotalWeight', $cartTotalWeight)
            ->orderBy('tp.maxWeight', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($transportPrice === null) {
            $message = sprintf('Transport price with domain ID "%d", transport ID "%d", and cart total weight %dg not found.', $domainId, $transport->getId(), $cartTotalWeight);

            throw new TransportPriceNotFoundException($message);
        }

        return $transportPrice;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;

class TransportPriceRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function getTransportPriceOnDomainByTransportAndClosestWeight(
        int $domainId,
        Transport $transport,
        int $cartTotalWeight,
        Currency $currency,
    ): TransportPrice {
        $transportPrice = $this->em->createQueryBuilder()
            ->select('tp')
            ->from(TransportPrice::class, 'tp')
            ->where('tp.transport = :transport')
            ->andWhere('tp.domainId = :domainId')
            ->andWhere('tp.currency = :currency')
            ->andWhere('((tp.maxWeight >= :cartTotalWeight) OR (tp.maxWeight IS NULL))')
            ->setParameter('transport', $transport)
            ->setParameter('domainId', $domainId)
            ->setParameter('currency', $currency)
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

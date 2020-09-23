<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPallet;

use App\Model\Transport\Transport;
use App\Model\Transport\TransportPallet\Exception\SuitableTransportPalletPriceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class TransportPalletPriceRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(TransportPalletPrice::class);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param int $domainId
     * @return \App\Model\Transport\TransportPallet\TransportPalletPrice[]
     */
    public function getSortedPalletPricesByTransportAndDomain(Transport $transport, int $domainId): array
    {
        return $this->getRepository()->findBy(
            [
                'transport' => $transport,
                'domainId' => $domainId,
            ],
            [
                'productsPriceTo' => 'ASC',
            ]
        );
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceByProductsPrice(Transport $transport, int $domainId, Price $productsPrice): Money
    {
        /** @var \App\Model\Transport\TransportPallet\TransportPalletPrice|null $transportPalletPrice */
        $transportPalletPrice = $this->getRepository()->createQueryBuilder('tpp')
            ->andWhere('tpp.transport = :transport')
            ->andWhere('tpp.domainId = :domainId')
            ->andWhere('tpp.productsPriceTo >= :productsPrice')
            ->orderBy('tpp.productsPriceTo', 'desc')
            ->setMaxResults(1)
            ->setParameters([
                'transport' => $transport,
                'domainId' => $domainId,
                'productsPrice' => $productsPrice->getPriceWithVat()->getAmount(),
            ])
            ->getQuery()
            ->getOneOrNullResult();

        if ($transportPalletPrice !== null) {
            return $transportPalletPrice->getTransportPrice();
        }

        $transportPalletPrice = $this->getRepository()->createQueryBuilder('tpp')
            ->andWhere('tpp.transport = :transport')
            ->andWhere('tpp.domainId = :domainId')
            ->andWhere('tpp.productsPriceTo IS NULL')
            ->setMaxResults(1)
            ->setParameters([
                'transport' => $transport,
                'domainId' => $domainId,
            ])
            ->getQuery()
            ->getOneOrNullResult();

        if ($transportPalletPrice !== null) {
            return $transportPalletPrice->getTransportPrice();
        }

        throw new SuitableTransportPalletPriceNotFoundException($transport, $domainId);
    }
}

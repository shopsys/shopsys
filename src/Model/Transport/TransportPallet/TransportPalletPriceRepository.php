<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPallet;

use App\Model\Transport\Transport;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

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
}

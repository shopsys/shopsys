<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPackage;

use App\Model\Transport\Transport;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class TransportPackageRepository
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
     * @return \Doctrine\Persistence\ObjectRepository
     */
    private function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(TransportPackage::class);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @return \App\Model\Transport\TransportPackage\TransportPackage[]
     */
    public function getTransportPackagesByTransport(Transport $transport): array
    {
        return $this->getRepository()->findBy(['transport' => $transport], ['domainId' => 'ASC', 'priceWithVat' => 'ASC']);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param int $domainId
     * @return \App\Model\Transport\TransportPackage\TransportPackage[]
     */
    public function getTransportPackagesByTransportAndDomainId(Transport $transport, int $domainId): array
    {
        return $this->getRepository()->findBy(
            [
                'transport' => $transport,
                'domainId' => $domainId,
            ],
            [
                'domainId' => 'ASC',
                'priceWithVat' => 'ASC',
            ]
        );
    }

    /**
     * @param int $transportPackageId
     * @return \App\Model\Transport\TransportPackage\TransportPackage|null
     */
    public function findById(int $transportPackageId): ?TransportPackage
    {
        return $this->getRepository()->find($transportPackageId);
    }
}

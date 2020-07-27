<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPackage;

use App\Model\Transport\Transport;
use App\Model\Transport\TransportData;
use Doctrine\ORM\EntityManagerInterface;

class TransportPackageFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageRepository
     */
    private TransportPackageRepository $transportPackageRepository;

    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageFactory
     */
    private TransportPackageFactory $transportPackageFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Model\Transport\TransportPackage\TransportPackageRepository $transportPackageRepository
     * @param \App\Model\Transport\TransportPackage\TransportPackageFactory $transportPackageFactory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TransportPackageRepository $transportPackageRepository,
        TransportPackageFactory $transportPackageFactory
    ) {
        $this->entityManager = $entityManager;
        $this->transportPackageRepository = $transportPackageRepository;
        $this->transportPackageFactory = $transportPackageFactory;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function updateTransportPackages(Transport $transport, TransportData $transportData): void
    {
        $oldTransportPackagesToDelete = $this->transportPackageRepository->getTransportPackagesByTransport($transport);
        foreach ($transportData->transportPackages as $transportPackageData) {
            $transportPackage = null;
            if ($transportPackageData->id !== null) {
                $transportPackage = $this->transportPackageRepository->findById((int)$transportPackageData->id);
            }

            if ($transportPackage === null) {
                $transportPackage = $this->transportPackageFactory->create($transportPackageData, $transport);
                $this->entityManager->persist($transportPackage);
            } else {
                $transportPackage->edit($transportPackageData);

                $oldTransportPackagesIndex = array_search($transportPackage, $oldTransportPackagesToDelete, true);
                unset($oldTransportPackagesToDelete[$oldTransportPackagesIndex]);
            }
        }

        foreach ($oldTransportPackagesToDelete as $transportPackageToDelete) {
            $this->entityManager->remove($transportPackageToDelete);
        }

        $this->entityManager->flush();
    }
}

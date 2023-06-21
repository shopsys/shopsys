<?php

declare(strict_types=1);

namespace App\Model\Transfer;

use Doctrine\ORM\EntityManagerInterface;

class TransferFacade
{
    /**
     * @param \App\Model\Transfer\TransferRepository $transferRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private TransferRepository $transferRepository,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \App\Model\Transfer\Transfer[]
     */
    public function getAll(): array
    {
        return $this->transferRepository->getAll();
    }

    /**
     * @param string $serviceTransferIdentifier
     * @return \App\Model\Transfer\Transfer
     */
    public function getTransferByIdentifier(string $serviceTransferIdentifier): Transfer
    {
        return $this->transferRepository->getTransferByIdentifier($serviceTransferIdentifier);
    }

    /**
     * @param string $identifier
     * @param string $name
     * @return \App\Model\Transfer\Transfer
     */
    public function create(string $identifier, string $name): Transfer
    {
        $transfer = new Transfer($identifier, $name);
        $this->em->persist($transfer);
        $this->em->flush();

        return $transfer;
    }
}

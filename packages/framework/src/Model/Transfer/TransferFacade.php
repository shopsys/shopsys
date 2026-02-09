<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

use Doctrine\ORM\EntityManagerInterface;

class TransferFacade
{
    public function __construct(
        protected readonly TransferRepository $transferRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly TransferFactory $transferFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Transfer[]
     */
    public function getAll(): array
    {
        return $this->transferRepository->getAll();
    }

    public function getTransferByIdentifier(string $serviceTransferIdentifier): Transfer
    {
        return $this->transferRepository->getTransferByIdentifier($serviceTransferIdentifier);
    }

    public function create(string $identifier, string $name): Transfer
    {
        $transfer = $this->transferFactory->create($identifier, $name);
        $this->em->persist($transfer);
        $this->em->flush();

        return $transfer;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

use Doctrine\ORM\EntityManagerInterface;

class TransferFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transfer\TransferRepository $transferRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Transfer\TransferFactory $transferFactory
     */
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

    /**
     * @param string $serviceTransferIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Transfer
     */
    public function getTransferByIdentifier(string $serviceTransferIdentifier): Transfer
    {
        return $this->transferRepository->getTransferByIdentifier($serviceTransferIdentifier);
    }

    /**
     * @param string $identifier
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Transfer
     */
    public function create(string $identifier, string $name): Transfer
    {
        $transfer = $this->transferFactory->create($identifier, $name);
        $this->em->persist($transfer);
        $this->em->flush();

        return $transfer;
    }
}

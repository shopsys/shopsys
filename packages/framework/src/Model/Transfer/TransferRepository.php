<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Transfer\Exception\UnknownServiceTransferException;

class TransferRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(Transfer::class);
    }

    /**
     * @param string $identifier
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Transfer
     */
    public function getTransferByIdentifier(string $identifier): Transfer
    {
        $transfer = $this->getRepository()->findOneBy(['identifier' => $identifier]);

        if ($transfer === null) {
            throw new UnknownServiceTransferException($identifier);
        }

        return $transfer;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Transfer[]
     */
    public function getAll(): array
    {
        return $this->getRepository()->findAll();
    }
}

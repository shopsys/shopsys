<?php

declare(strict_types=1);

namespace App\Model\Transfer;

use App\Model\Transfer\Exception\UnknownServiceTransferException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class TransferRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(Transfer::class);
    }

    /**
     * @param string $identifier
     * @return \App\Model\Transfer\Transfer
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
     * @return \App\Model\Transfer\Transfer[]
     */
    public function getAll(): array
    {
        return $this->getRepository()->findAll();
    }
}

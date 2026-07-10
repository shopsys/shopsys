<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportGroupNotFoundException;

class TransportGroupRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function getById(int $transportGroupId): TransportGroup
    {
        $transportGroup = $this->em->find(TransportGroup::class, $transportGroupId);

        if ($transportGroup === null) {
            throw new TransportGroupNotFoundException($transportGroupId);
        }

        return $transportGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportGroup[]
     */
    public function getAll(): array
    {
        return $this->em->getRepository(TransportGroup::class)->findBy([], ['position' => 'asc']);
    }
}

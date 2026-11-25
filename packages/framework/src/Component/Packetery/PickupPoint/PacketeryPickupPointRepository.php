<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PacketeryPickupPointRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getPacketeryPickupPointRepository(): EntityRepository
    {
        return $this->em->getRepository(PacketeryPickupPoint::class);
    }

    public function findById(string $id): ?PacketeryPickupPoint
    {
        return $this->getPacketeryPickupPointRepository()->find($id);
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPoint>
     */
    public function getAllIndexedById(): array
    {
        return $this->getPacketeryPickupPointRepository()
            ->createQueryBuilder('pp')
            ->indexBy('pp', 'pp.id')
            ->getQuery()
            ->getResult();
    }
}

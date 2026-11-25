<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PacketeryPickupPointFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(PacketeryPickupPointData $data): PacketeryPickupPoint
    {
        $entityClassName = $this->entityNameResolver->resolve(PacketeryPickupPoint::class);

        return new $entityClassName($data);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OrderNumberSequenceFactory implements OrderNumberSequenceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param int $id
     * @param string $number
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequence
     */
    public function create(int $id, string $number): OrderNumberSequence
    {
        $classData = $this->entityNameResolver->resolve(OrderNumberSequence::class);

        return new $classData($id, $number);
    }
}

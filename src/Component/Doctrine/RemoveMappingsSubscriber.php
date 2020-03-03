<?php

declare(strict_types=1);

namespace App\Component\Doctrine;

use App\Model\Order\Order;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

class RemoveMappingsSubscriber implements EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        // Remove Order::$transport because Order has more transports depended on ProductType
        if ($classMetadata->rootEntityName === Order::class) {
            $associationMappings = $classMetadata->associationMappings;
            unset($associationMappings['transport']);
            $classMetadata->associationMappings = $associationMappings;
        }
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\EntityLog\ParentResolver\EntityLogParentResolverInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class ImageEntityLogParentResolver implements EntityLogParentResolverInterface
{
    public function __construct(
        protected readonly ImageConfig $imageConfig,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    #[Override]
    public function supports(object $entity): bool
    {
        return $entity instanceof Image;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $entity
     */
    #[Override]
    public function resolveParent(object $entity): ?object
    {
        foreach ($this->imageConfig->getAllImageEntityConfigsByClass() as $imageEntityConfig) {
            if ($imageEntityConfig->getEntityName() === $entity->getEntityName()) {
                // the parent being edited is in the identity map already, so no query is executed
                return $this->em->getReference($imageEntityConfig->getEntityClass(), $entity->getEntityId());
            }
        }

        return null;
    }
}

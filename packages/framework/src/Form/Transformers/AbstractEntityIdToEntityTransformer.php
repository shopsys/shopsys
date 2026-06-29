<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;

abstract class AbstractEntityIdToEntityTransformer implements DataTransformerInterface
{
    /**
     * @param object|null $entity
     */
    #[Override]
    public function transform($entity): ?int
    {
        if ($entity === null) {
            return null;
        }

        return $this->getEntityId($entity);
    }

    /**
     * @param int|string|null $entityId
     */
    #[Override]
    public function reverseTransform($entityId): ?object
    {
        $entityId = (int)$entityId;

        if ($entityId === 0) {
            return null;
        }

        return $this->getEntityById($entityId);
    }

    abstract protected function getEntityId(object $entity): int;

    abstract protected function getEntityById(int $entityId): object;
}

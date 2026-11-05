<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

abstract class AbstractEntityIdToEntityTransformer implements DataTransformerInterface
{
    /**
     * The greatest identifier an entity can have — identifiers are generated into a column of the integer type,
     * and the database refuses to look up a greater one rather than finding nothing.
     */
    protected const int MAXIMUM_ENTITY_ID = 2147483647;

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
        $submittedEntityId = $entityId;
        $entityId = (int)$entityId;

        if ($entityId === 0) {
            return null;
        }

        // an identifier out of the range of the column (edited by hand) is no entity
        if ($entityId < 0 || $entityId > static::MAXIMUM_ENTITY_ID) {
            throw new TransformationFailedException(sprintf('No entity can be identified by "%s".', $submittedEntityId));
        }

        return $this->getEntityById($entityId);
    }

    abstract protected function getEntityId(object $entity): int;

    abstract protected function getEntityById(int $entityId): object;
}

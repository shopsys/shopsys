<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionDataProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IdsToEntitiesTransformer implements DataTransformerInterface
{
    public function __construct(protected readonly TreeSelectionDataProviderInterface $treeSelectionDataProvider)
    {
    }

    /**
     * @param iterable<\Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface>|null $entities
     * @return int[]
     */
    #[Override]
    public function transform($entities): array
    {
        $entityIds = [];

        if (is_iterable($entities)) {
            foreach ($entities as $entity) {
                $entityIds[] = $entity->getId();
            }
        }

        return $entityIds;
    }

    /**
     * @param string[]|int[]|null $entityIds
     * @return \Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface[]
     */
    #[Override]
    public function reverseTransform($entityIds): array
    {
        if (!is_array($entityIds) || $entityIds === []) {
            return [];
        }

        $normalizedIds = array_map('intval', $entityIds);
        $entities = $this->treeSelectionDataProvider->getByIds($normalizedIds);
        $selectedEntitiesById = [];
        $selectedEntities = [];

        foreach ($entities as $entity) {
            $selectedEntitiesById[$entity->getId()] = $entity;
        }

        foreach ($normalizedIds as $normalizedId) {
            $selectedEntities[] = $selectedEntitiesById[$normalizedId]
                ?? throw new TransformationFailedException(sprintf('Entity with id %d not found', $normalizedId));
        }

        return $selectedEntities;
    }
}

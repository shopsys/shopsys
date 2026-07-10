<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Helper;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use RuntimeException;

/**
 * Resolves the identifier of a Doctrine entity managed by the CRUD system.
 *
 * CRUD only supports entities with a single (non-composite) primary key.
 */
final readonly class CrudEntityIdentifierExtractor
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function getId(object $entity): int
    {
        $classMetadata = $this->getClassMetadata($entity::class);

        return (int)$classMetadata->getIdentifierValues($entity)[$this->getSingleIdentifierFieldName($classMetadata)];
    }

    /**
     * Ensures the entity can be managed by CRUD (single-column primary key).
     *
     * @param class-string $entityClass
     */
    public function assertSupportedEntity(string $entityClass): void
    {
        $this->getSingleIdentifierFieldName($this->getClassMetadata($entityClass));
    }

    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata<object> $classMetadata
     */
    private function getSingleIdentifierFieldName(ClassMetadata $classMetadata): string
    {
        $identifierFieldNames = $classMetadata->getIdentifierFieldNames();

        if (count($identifierFieldNames) !== 1) {
            throw new RuntimeException('Crud controller does not support entities with composite primary keys.');
        }

        return $identifierFieldNames[0];
    }

    /**
     * @param class-string $entityClass
     * @return \Doctrine\Persistence\Mapping\ClassMetadata<object>
     */
    private function getClassMetadata(string $entityClass): ClassMetadata
    {
        $entityManager = $this->managerRegistry->getManagerForClass($entityClass);

        if ($entityManager === null) {
            throw new RuntimeException(sprintf('No entity manager found for class "%s".', $entityClass));
        }

        return $entityManager->getClassMetadata($entityClass);
    }
}

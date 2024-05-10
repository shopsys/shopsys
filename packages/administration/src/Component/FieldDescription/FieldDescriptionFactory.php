<?php

declare(strict_types=1);

namespace Shopsys\Administration\Component\FieldDescription;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use UnexpectedValueException;
use function array_slice;
use function count;

class FieldDescriptionFactory implements FieldDescriptionFactoryInterface
{
    /**
     * @param \Symfony\Bridge\Doctrine\ManagerRegistry $registry
     */
    public function __construct(
        protected readonly ManagerRegistry $registry,
    ) {
    }

    /**
     * @param string $class
     * @param string $name
     * @param array $options
     * @return \Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface
     */
    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
        $class = preg_replace('/' . preg_quote('Data', '/') . '$/', '', $class);

        [$metadata, $propertyName, $parentAssociationMappings] = $this->getParentMetadataForProperty($class, $name);

        return new FieldDescription(
            $name,
            $options,
            $metadata->fieldMappings[$propertyName] ?? [],
            $metadata->associationMappings[$propertyName] ?? [],
            $parentAssociationMappings,
            $propertyName,
        );
    }

    /**
     * @phpstan-param class-string $baseClass
     * @phpstan-return array{\Doctrine\ORM\Mapping\ClassMetadata<object>, string, mixed[]}
     * @param string $baseClass
     * @param string $propertyFullName
     * @return array
     */
    protected function getParentMetadataForProperty(string $baseClass, string $propertyFullName): array
    {
        $nameElements = explode('.', $propertyFullName);
        $lastPropertyName = array_pop($nameElements);
        $class = $baseClass;
        $parentAssociationMappings = [];

        foreach ($nameElements as $nameElement) {
            $metadata = $this->getMetadata($class);

            if (!isset($metadata->associationMappings[$nameElement])) {
                break;
            }

            $parentAssociationMappings[] = $metadata->associationMappings[$nameElement];
            $class = $metadata->getAssociationTargetClass($nameElement);
        }

        $properties = array_slice($nameElements, count($parentAssociationMappings));
        $properties[] = $lastPropertyName;

        return [
            $this->getMetadata($class),
            implode('.', $properties),
            $parentAssociationMappings,
        ];
    }

    /**
     * @phpstan-template TObject of object
     * @phpstan-param class-string<TObject> $class
     * @phpstan-return \Doctrine\ORM\Mapping\ClassMetadata<TObject>
     * @param string $class
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected function getMetadata(string $class): ClassMetadata
    {
        return $this->getEntityManager($class)->getClassMetadata($class);
    }

    /**
     * @param class-string $class
     * @throw \UnexpectedValueException
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    protected function getEntityManager(string $class): EntityManagerInterface
    {
        $em = $this->registry->getManagerForClass($class);

        if (!$em instanceof EntityManagerInterface) {
            throw new UnexpectedValueException(sprintf('No entity manager defined for class "%s".', $class));
        }

        return $em;
    }
}

<?php

declare(strict_types=1);

namespace App\Component\Doctrine;

use App\Model\Order\PromoCode\PromoCode;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Product;

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

        $this->removeColumnsFromEntityMappings(Parameter::class, ['visible'], $classMetadata);

        $this->removeColumnsFromEntityMappings(
            Product::class,
            [
                'outOfStockAvailability',
                'outOfStockAction',
                'stockQuantity',
                'usingStock',
            ],
            $classMetadata,
        );

        $this->removeColumnsFromEntityMappings(PromoCode::class, ['percent'], $classMetadata);
    }

    /**
     * @param string $parentClassName
     * @param string[] $attributeNames
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
     */
    private function removeColumnsFromEntityMappings(
        string $parentClassName,
        array $attributeNames,
        ClassMetadata $classMetadata,
    ): void {
        if ($classMetadata->rootEntityName === $parentClassName || is_subclass_of($classMetadata->rootEntityName, $parentClassName)) {
            foreach ($attributeNames as $attributeName) {
                $classMetadata->associationMappings = $this->removeMappingByKey($attributeName, $classMetadata->associationMappings);
                $classMetadata->fieldMappings = $this->removeMappingByKey($attributeName, $classMetadata->fieldMappings);
                $classMetadata->columnNames = $this->removeMappingByKey($attributeName, $classMetadata->columnNames);

                $classMetadata->fieldNames = $this->removeMappingByValue($attributeName, $classMetadata->fieldNames);
            }
        }
    }

    /**
     * @param string $attributeName
     * @param array<string, array<string, mixed>> $mapping
     * @return array<string, array<string, mixed>>
     */
    private function removeMappingByKey(string $attributeName, array $mapping): array
    {
        if (array_key_exists($attributeName, $mapping)) {
            unset($mapping[$attributeName]);
        }

        return $mapping;
    }

    /**
     * @param string $attributeName
     * @param string[] $mapping
     * @return string[]
     */
    private function removeMappingByValue(string $attributeName, array $mapping): array
    {
        $key = array_search($attributeName, $mapping, true);

        if ($key !== false) {
            unset($mapping[$key]);
        }

        return $mapping;
    }
}

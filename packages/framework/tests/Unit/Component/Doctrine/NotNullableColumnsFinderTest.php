<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ManyToOneAssociationMapping;
use Doctrine\ORM\Mapping\OneToManyAssociationMapping;
use Doctrine\Persistence\Mapping\ClassMetadata as PersistenceClassMetadata;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\Doctrine\NotNullableColumnsFinder;
use stdClass;

class NotNullableColumnsFinderTest extends TestCase
{
    public function testGetAllNotNullableColumnNamesIndexedByTableName(): void
    {
        $classMetadataInfoStub = $this->createStub(ClassMetadata::class);
        $classMetadataInfoStub
            ->method('getTableName')
            ->willReturn('EntityName');
        $classMetadataInfoStub
            ->method('getFieldNames')
            ->willReturn(['notNullableField', 'nullableField']);
        $classMetadataInfoStub
            ->method('isNullable')
            ->willReturnCallback(function ($fieldName) {
                return $fieldName === 'nullableField';
            });
        $classMetadataInfoStub
            ->method('getColumnName')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'notNullableField') {
                    return 'not_nullable_field';
                }
            });

        $classMetadataInfoStub
            ->method('getAssociationMappings')
            ->willReturn($this->getAssociationMappings());

        $expectedResult = [
            'EntityName' => [
                'not_nullable_field',
                'not_nullable_association',
            ],
        ];

        $notNullableColumnsFinder = new NotNullableColumnsFinder();
        $actualResult = $notNullableColumnsFinder->getAllNotNullableColumnNamesIndexedByTableName(
            [$classMetadataInfoStub],
        );

        $this->assertSame($expectedResult, $actualResult);
    }

    private function getAssociationMappings(): array
    {
        $associationMapping1 = ManyToOneAssociationMapping::fromMappingArray([
            'fieldName' => 'nullableAssociation',
            'sourceEntity' => stdClass::class,
            'targetEntity' => stdClass::class,
            'isOwningSide' => true,
            'joinColumns' => [['name' => 'nullable_association', 'referencedColumnName' => 'id', 'nullable' => true]],
        ]);

        $associationMapping2 = ManyToOneAssociationMapping::fromMappingArray([
            'fieldName' => 'notNullableAssociation',
            'sourceEntity' => stdClass::class,
            'targetEntity' => stdClass::class,
            'isOwningSide' => true,
            'joinColumns' => [['name' => 'not_nullable_association', 'referencedColumnName' => 'id', 'nullable' => false]],
        ]);

        // inverse side of bidirectional association (no joinColumns property)
        $associationMapping3 = OneToManyAssociationMapping::fromMappingArray([
            'fieldName' => 'inverseSide',
            'sourceEntity' => stdClass::class,
            'targetEntity' => stdClass::class,
            'isOwningSide' => false,
            'mappedBy' => 'owner',
        ]);

        return [$associationMapping1, $associationMapping2, $associationMapping3];
    }

    public function testGetAllNotNullableColumnNamesIndexedByTableNameException(): void
    {
        $classMetadataStub = $this->createStub(PersistenceClassMetadata::class);
        $this->expectException(UnexpectedTypeException::class);

        $notNullableColumnsFinder = new NotNullableColumnsFinder();
        $notNullableColumnsFinder->getAllNotNullableColumnNamesIndexedByTableName([$classMetadataStub]);
    }
}

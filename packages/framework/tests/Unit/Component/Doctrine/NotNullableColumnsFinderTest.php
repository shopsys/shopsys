<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadata as PersistenceClassMetadata;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\Doctrine\NotNullableColumnsFinder;

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
        $associationMapping1['joinColumns'] = [
            [
                'nullable' => true,
                'name' => 'nullable_association',
            ],
        ];
        $associationMapping2['joinColumns'] = [
            [
                'nullable' => false,
                'name' => 'not_nullable_association',
            ],
        ];

        // this array can simulate bidirectional association
        $associationMapping3 = [];

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

<?php

namespace Tests\FrameworkBundle\Unit\Component\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\NotNullableColumnsFinder;

class NotNullableColumnsFinderTest extends TestCase
{
    public function testGetAllNotNullableColumnNamesIndexedByTableName()
    {
        $classMetadataInfoMock = $this->createMock(ClassMetadataInfo::class);
        $classMetadataInfoMock
            ->method('getTableName')
            ->willReturn('EntityName');
        $classMetadataInfoMock
            ->method('getFieldNames')
            ->willReturn(['notNullableField', 'nullableField']);
        $classMetadataInfoMock
            ->method('isNullable')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'nullableField') {
                    return true;
                }
                return false;
            });
        $classMetadataInfoMock
            ->method('getColumnName')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'notNullableField') {
                    return 'not_nullable_field';
                }
            });

        $classMetadataInfoMock
            ->method('getAssociationMappings')
            ->willReturn($this->getAssociationMappings());

        $expectedResult = [
            'EntityName' => [
                'not_nullable_field',
                'not_nullable_association',
            ],
        ];

        $notNullableColumnsFinder = new NotNullableColumnsFinder();
        $actualResult = $notNullableColumnsFinder->getAllNotNullableColumnNamesIndexedByTableName([$classMetadataInfoMock]);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
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

    public function testGetAllNotNullableColumnNamesIndexedByTableNameException()
    {
        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $this->expectException(\Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException::class);

        $notNullableColumnsFinder = new NotNullableColumnsFinder();
        $notNullableColumnsFinder->getAllNotNullableColumnNamesIndexedByTableName([$classMetadataMock]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadata as PersistenceClassMetadata;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\Doctrine\StringColumnsFinder;

class StringColumnsFinderTest extends TestCase
{
    public function testGetAllStringColumnNamesIndexedByTableName(): void
    {
        $classMetadataInfoStub = $this->createStub(ClassMetadata::class);
        $classMetadataInfoStub
            ->method('getTableName')
            ->willReturn('EntityName');
        $classMetadataInfoStub
            ->method('getFieldNames')
            ->willReturn(['stringField', 'textField', 'otherField']);
        $classMetadataInfoStub
            ->method('getTypeOfField')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'stringField') {
                    return 'string';
                }

                if ($fieldName === 'textField') {
                    return 'text';
                }

                return 'other';
            });
        $classMetadataInfoStub
            ->method('getColumnName')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'stringField') {
                    return 'string_field';
                }

                if ($fieldName === 'textField') {
                    return 'text_field';
                }
            });

        $expectedResult = [
            'EntityName' => [
                'string_field',
                'text_field',
            ],
        ];

        $stringColumnsFinder = new StringColumnsFinder();
        $actualResult = $stringColumnsFinder->getAllStringColumnNamesIndexedByTableName([$classMetadataInfoStub]);

        $this->assertSame($expectedResult, $actualResult);
    }

    public function testGetAllStringColumnNamesIndexedByTableNameSkipsMappedSuperclasses(): void
    {
        $mappedSuperclassMetadataStub = $this->createStub(ClassMetadata::class);
        $mappedSuperclassMetadataStub->isMappedSuperclass = true;
        $mappedSuperclassMetadataStub
            ->method('getTableName')
            ->willReturn('MappedSuperclass');
        $mappedSuperclassMetadataStub
            ->method('getFieldNames')
            ->willReturn(['mappedSuperclassStringField']);
        $mappedSuperclassMetadataStub
            ->method('getTypeOfField')
            ->willReturn('string');
        $mappedSuperclassMetadataStub
            ->method('getColumnName')
            ->willReturn('mapped_superclass_string_field');

        $classMetadataInfoStub = $this->createStub(ClassMetadata::class);
        $classMetadataInfoStub
            ->method('getTableName')
            ->willReturn('EntityName');
        $classMetadataInfoStub
            ->method('getFieldNames')
            ->willReturn(['entityStringField']);
        $classMetadataInfoStub
            ->method('getTypeOfField')
            ->willReturn('string');
        $classMetadataInfoStub
            ->method('getColumnName')
            ->willReturn('entity_string_field');

        $expectedResult = [
            'EntityName' => [
                'entity_string_field',
            ],
        ];

        $stringColumnsFinder = new StringColumnsFinder();
        $actualResult = $stringColumnsFinder->getAllStringColumnNamesIndexedByTableName([
            $mappedSuperclassMetadataStub,
            $classMetadataInfoStub,
        ]);

        $this->assertSame($expectedResult, $actualResult);
    }

    public function testGetAllStringColumnNamesIndexedByTableNameException(): void
    {
        $classMetadataStub = $this->createStub(PersistenceClassMetadata::class);
        $this->expectException(UnexpectedTypeException::class);

        $stringColumnsFinder = new StringColumnsFinder();
        $stringColumnsFinder->getAllStringColumnNamesIndexedByTableName([$classMetadataStub]);
    }
}

<?php

namespace Tests\FrameworkBundle\Unit\Component\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\StringColumnsFinder;

class StringColumnsFinderTest extends TestCase
{
    public function testGetAllStringColumnNamesIndexedByTableName()
    {
        $classMetadataInfoMock = $this->createMock(ClassMetadataInfo::class);
        $classMetadataInfoMock
            ->method('getTableName')
            ->willReturn('EntityName');
        $classMetadataInfoMock
            ->method('getFieldNames')
            ->willReturn(['stringField', 'textField', 'otherField']);
        $classMetadataInfoMock
            ->method('getTypeOfField')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'stringField') {
                    return 'string';
                } elseif ($fieldName === 'textField') {
                    return 'text';
                }
                return 'other';
            });
        $classMetadataInfoMock
            ->method('getColumnName')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'stringField') {
                    return 'string_field';
                } elseif ($fieldName === 'textField') {
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
        $actualResult = $stringColumnsFinder->getAllStringColumnNamesIndexedByTableName([$classMetadataInfoMock]);

        $this->assertSame($expectedResult, $actualResult);
    }

    public function testGetAllStringColumnNamesIndexedByTableNameException()
    {
        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $this->expectException(\Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException::class);

        $stringColumnsFinder = new StringColumnsFinder();
        $stringColumnsFinder->getAllStringColumnNamesIndexedByTableName([$classMetadataMock]);
    }
}

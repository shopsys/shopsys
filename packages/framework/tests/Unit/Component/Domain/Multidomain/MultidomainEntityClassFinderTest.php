<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain\Multidomain;

use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassFinder;

class MultidomainEntityClassFinderTest extends TestCase
{
    public function testGetMultidomainEntitiesNames(): void
    {
        $classMetadataStub1 = $this->createStub(ClassMetadata::class);
        $classMetadataStub1
            ->method('getIdentifierFieldNames')
            ->willReturn(['id', 'testId']);
        $classMetadataStub1
            ->method('getName')
            ->willReturn('NonMultidomainClass1');

        $classMetadataStub2 = $this->createStub(ClassMetadata::class);
        $classMetadataStub2
            ->method('getIdentifierFieldNames')
            ->willReturn(['domainId']);
        $classMetadataStub2
            ->method('getName')
            ->willReturn('NonMultidomainClass2');

        $classMetadataStub3 = $this->createStub(ClassMetadata::class);
        $classMetadataStub3
            ->method('getIdentifierFieldNames')
            ->willReturn(['id', 'domainId']);
        $classMetadataStub3
            ->method('getName')
            ->willReturn('MultidomainClass');
        $allClassesMetadata = [$classMetadataStub1, $classMetadataStub2, $classMetadataStub3];

        $multidomainEntityClassFinder = new MultidomainEntityClassFinder();
        $multidomainEntitiesNames = $multidomainEntityClassFinder->getMultidomainEntitiesNames(
            $allClassesMetadata,
            [],
            [],
        );

        $this->assertSame(['MultidomainClass'], $multidomainEntitiesNames);
    }
}

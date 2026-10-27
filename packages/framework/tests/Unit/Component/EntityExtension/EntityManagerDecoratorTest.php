<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\EntityExtension;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ManyToOneAssociationMapping;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use stdClass;

class EntityManagerDecoratorTest extends TestCase
{
    public function testEntitiesOfClassWithCascadingRefreshAreRefreshedOneByOne(): void
    {
        $firstEntity = new stdClass();
        $secondEntity = new stdClass();
        $classMetadata = new ClassMetadata(stdClass::class);
        $classMetadata->associationMappings = [
            'relation' => ManyToOneAssociationMapping::fromMappingArray([
                'fieldName' => 'relation',
                'sourceEntity' => stdClass::class,
                'targetEntity' => stdClass::class,
                'cascade' => ['refresh'],
                'isOwningSide' => true,
                'joinColumns' => [['name' => 'relation_id', 'referencedColumnName' => 'id']],
            ]),
        ];

        $unitOfWorkStub = $this->createStub(UnitOfWork::class);
        $unitOfWorkStub->method('getIdentityMap')
            ->willReturn([stdClass::class => [$firstEntity, $secondEntity]]);

        $refreshedEntities = [];
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->method('getUnitOfWork')->willReturn($unitOfWorkStub);
        $entityManagerMock->method('isUninitializedObject')->willReturn(false);
        $entityManagerMock->method('getClassMetadata')->willReturn($classMetadata);
        $entityManagerMock->expects($this->exactly(2))
            ->method('refresh')
            ->willReturnCallback(static function (object $entity) use (&$refreshedEntities): void {
                $refreshedEntities[] = $entity;
            });

        $entityManagerDecorator = new EntityManagerDecorator(
            $entityManagerMock,
            $this->createStub(Configuration::class),
            new EntityNameResolver([]),
        );
        $entityManagerDecorator->refreshLoadedEntitiesByClassName(stdClass::class);

        $this->assertSame([$firstEntity, $secondEntity], $refreshedEntities);
    }
}

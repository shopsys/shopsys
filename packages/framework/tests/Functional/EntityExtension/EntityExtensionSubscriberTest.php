<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Functional\EntityExtension;

use Tests\FrameworkBundle\Functional\EntityExtension\Model\DummyEntity;
use Tests\FrameworkBundle\Functional\EntityExtension\Model\ExtendedDummyEntity;
use Tests\App\Test\TransactionFunctionalTestCase;

class EntityExtensionSubscriberTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private EntityExtensionTestHelper $entityExtensionTestHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityExtensionTestHelper->registerTestEntities();
        $entityExtensionMap = [
            DummyEntity::class => ExtendedDummyEntity::class,
        ];
        $this->entityExtensionTestHelper->overwriteEntityExtensionMapInServicesInContainer($entityExtensionMap);
    }

    public function testLoadClassMetadataForEntityWithOverriddenAssociation(): void
    {
        $classMetadata = $this->em->getClassMetadata(ExtendedDummyEntity::class);

        $expectedOrderByValue = ['id' => 'DESC'];
        $this->assertEquals($expectedOrderByValue, $classMetadata->associationMappings['flags']['orderBy']);
    }
}

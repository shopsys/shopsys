<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension;

use Tests\App\Functional\EntityExtension\Model\DummyEntity;
use Tests\App\Functional\EntityExtension\Model\ExtendedDummyEntity;
use Tests\App\Test\TransactionFunctionalTestCase;

class EntityExtensionSubscriberTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Tests\App\Functional\EntityExtension\EntityExtensionTestHelper
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
        // @phpstan-ignore-next-line
        $this->assertEquals($expectedOrderByValue, $classMetadata->associationMappings['flags']['orderBy']);
    }
}

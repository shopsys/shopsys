<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension;

use Tests\App\Functional\EntityExtension\Model\DummyEntity;
use Tests\App\Functional\EntityExtension\Model\ExtendedDummyEntity;
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

        if (!$this->isMonorepo()) {
            $this->markTestSkipped('This test is run only in monorepo.');
        }

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

    /**
     * @return bool
     */
    private function isMonorepo(): bool
    {
        return file_exists(__DIR__ . '/../../../../../../parameters_monorepo.yaml');
    }
}

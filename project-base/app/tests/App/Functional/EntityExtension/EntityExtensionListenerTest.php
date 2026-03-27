<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension;

use Doctrine\ORM\Mapping\ManyToManyOwningSideMapping;
use Override;
use Tests\App\Functional\EntityExtension\Model\DummyEntity;
use Tests\App\Functional\EntityExtension\Model\ExtendedDummyEntity;
use Tests\App\Test\TransactionFunctionalTestCase;

class EntityExtensionListenerTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private EntityExtensionTestHelper $entityExtensionTestHelper;

    #[Override]
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

        $mapping = $classMetadata->getAssociationMapping('flags');
        $this->assertInstanceOf(ManyToManyOwningSideMapping::class, $mapping);
        $this->assertEquals(['id' => 'DESC'], $mapping->orderBy);
    }

    private function isMonorepo(): bool
    {
        return file_exists(__DIR__ . '/../../../../../../parameters_monorepo.yaml');
    }
}

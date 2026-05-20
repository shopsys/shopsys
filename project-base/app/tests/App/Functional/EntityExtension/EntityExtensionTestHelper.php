<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Tests\App\Test\Doctrine\AttributeMappedEntityHelper;

class EntityExtensionTestHelper
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly OverwritableEntityNameResolver $overwritableEntityNameResolver,
        private readonly OverwritableEntityExtensionListener $overwritableEntityExtensionListener,
    ) {
    }

    /**
     * @param string[] $entityExtensionMap
     */
    public function overwriteEntityExtensionMapInServicesInContainer(array $entityExtensionMap): void
    {
        $this->overwritableEntityExtensionListener->overwriteEntityExtensionMap($entityExtensionMap);
        $this->overwritableEntityNameResolver->overwriteEntityExtensionMap($entityExtensionMap);
    }

    public function registerTestEntities(): void
    {
        AttributeMappedEntityHelper::register(
            $this->em,
            [__DIR__ . '/Model'],
            'Tests\\App\\Functional\\EntityExtension',
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension;

use Doctrine\Bundle\DoctrineBundle\Mapping\MappingDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use PHPUnit\Framework\Assert;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;

class EntityExtensionTestHelper
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Tests\App\Functional\EntityExtension\OverwritableEntityNameResolver $overwritableEntityNameResolver
     * @param \Tests\App\Functional\EntityExtension\OverwritableEntityExtensionSubscriber $overwritableEntityExtensionSubscriber
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly OverwritableEntityNameResolver $overwritableEntityNameResolver,
        private readonly OverwritableEntityExtensionSubscriber $overwritableEntityExtensionSubscriber
    ) {
    }

    /**
     * @param string[] $entityExtensionMap
     */
    public function overwriteEntityExtensionMapInServicesInContainer(array $entityExtensionMap): void
    {
        $this->overwritableEntityExtensionSubscriber->overwriteEntityExtensionMap($entityExtensionMap);
        $this->overwritableEntityNameResolver->overwriteEntityExtensionMap($entityExtensionMap);
    }

    public function registerTestEntities(): void
    {
        $driver = new AnnotationDriver(new AnnotationReader(), __DIR__ . '/Model');

        $configuration = $this->em->getConfiguration();
        $mappingDriver = $configuration->getMetadataDriverImpl();
        if ($mappingDriver instanceof MappingDriver) {
            $metadataDriverChain = $mappingDriver->getDriver();
            if ($metadataDriverChain instanceof MappingDriverChain) {
                $metadataDriverChain->addDriver($driver, 'Tests\\App\\Functional\\EntityExtension');
            } else {
                Assert::fail(sprintf('Metadata driver must be type of %s', MappingDriverChain::class));
            }
        } else {
            Assert::fail(sprintf('Mapping driver must be type of %s, null given', MappingDriver::class));
        }
    }
}

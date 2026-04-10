<?php

declare(strict_types=1);

namespace Tests\App\Test\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Mapping\MappingDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use PHPUnit\Framework\Assert;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;

final class AttributeMappedEntityHelper
{
    /**
     * @param array<int, string> $paths
     */
    public static function register(EntityManagerDecorator $entityManager, array $paths, string $namespacePrefix): void
    {
        $driver = new AttributeDriver($paths);

        $configuration = $entityManager->getConfiguration();
        $mappingDriver = $configuration->getMetadataDriverImpl();

        if (!$mappingDriver instanceof MappingDriver) {
            Assert::fail(sprintf('Mapping driver must be type of %s.', MappingDriver::class));
        }

        $metadataDriverChain = $mappingDriver->getDriver();

        if (!$metadataDriverChain instanceof MappingDriverChain) {
            Assert::fail(sprintf('Metadata driver must be type of %s.', MappingDriverChain::class));
        }

        $metadataDriverChain->addDriver($driver, $namespacePrefix);
    }
}

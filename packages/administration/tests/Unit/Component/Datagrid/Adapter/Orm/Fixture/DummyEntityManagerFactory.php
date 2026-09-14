<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Shopsys\FrameworkBundle\Component\Doctrine\NormalizedFunction;

/**
 * The entity manager over the entities of this directory, the way the application configures one.
 */
final class DummyEntityManagerFactory
{
    public static function create(): EntityManager
    {
        $configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__], true);
        $configuration->addCustomStringFunction('NORMALIZED', NormalizedFunction::class);

        // the server version is pinned so that the PostgreSQL platform is resolved without connecting to a database
        $connection = DriverManager::getConnection(
            [
                'driver' => 'pdo_pgsql',
                'serverVersion' => '16',
            ],
            $configuration,
        );

        return new EntityManager($connection, $configuration);
    }
}

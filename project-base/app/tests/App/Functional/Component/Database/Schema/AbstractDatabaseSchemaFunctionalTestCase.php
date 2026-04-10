<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Override;
use Tests\App\Test\Doctrine\AttributeMappedEntityHelper;
use Tests\App\Test\TransactionFunctionalTestCase;

abstract class AbstractDatabaseSchemaFunctionalTestCase extends TransactionFunctionalTestCase
{
    protected const string TEST_ENTITY_NAMESPACE_PREFIX = 'Tests\\App\\Functional\\Component\\Database\\Schema\\Model\\';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipTestIfNotRunningInMonorepo();
        $this->setUpTestSchema();
    }

    protected function setUpTestSchema(): void
    {
        $this->prepareSchemaForTestEntities(
            $this->getTestEntityPaths(),
            static::TEST_ENTITY_NAMESPACE_PREFIX,
        );
    }

    /**
     * @param array<int, string> $paths
     */
    protected function prepareSchemaForTestEntities(array $paths, string $namespacePrefix): void
    {
        $this->registerTestEntities($paths, $namespacePrefix);
        $metadata = $this->getMetadata($namespacePrefix);

        if ($metadata === []) {
            return;
        }

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->updateSchema($metadata);
    }

    /**
     * @return array<int, string>
     */
    protected function getTestEntityPaths(): array
    {
        return [__DIR__ . '/Model'];
    }

    /**
     * @param array<int, string> $paths
     */
    protected function registerTestEntities(array $paths, string $namespacePrefix): void
    {
        AttributeMappedEntityHelper::register(
            $this->em,
            $paths,
            $namespacePrefix,
        );
    }

    /**
     * @return array<int, \Doctrine\Persistence\Mapping\ClassMetadata>
     */
    protected function getMetadata(string $namespacePrefix): array
    {
        return array_values(array_filter(
            $this->em->getMetadataFactory()->getAllMetadata(),
            fn (ClassMetadata $classMetadata): bool => str_starts_with($classMetadata->getName(), $namespacePrefix),
        ));
    }
}

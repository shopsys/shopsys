<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Database\Schema;

use LogicException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\McpBundle\Component\Database\Schema\AllowedDatabaseColumnsProvider;

class AllowedDatabaseColumnsProviderValidationTest extends AbstractDatabaseSchemaFunctionalTestCase
{
    protected const string TEST_ENTITY_NAMESPACE_PREFIX = 'Tests\\App\\Functional\\Component\\Database\\Schema\\InvalidModel\\';

    /**
     * @inject
     */
    private AllowedDatabaseColumnsProvider $allowedDatabaseColumnsProvider;

    /**
     * @return iterable<string, array{directoryName: string}>
     */
    public static function getInvalidEntityDirectories(): iterable
    {
        yield 'unknown inherited fieldName' => [
            'directoryName' => 'UnknownFieldName',
        ];

        yield 'duplicate inherited fieldName' => [
            'directoryName' => 'DuplicateFieldName',
        ];
    }

    #[DataProvider('getInvalidEntityDirectories')]
    public function testGetAllAllowedColumnsSetIndexedByTableNamesThrowsLogicExceptionForInvalidConfiguration(
        string $directoryName,
    ): void {
        $this->prepareSchemaForTestEntities(
            [__DIR__ . '/InvalidModel/' . $directoryName],
            static::TEST_ENTITY_NAMESPACE_PREFIX . $directoryName . '\\',
        );

        $this->expectException(LogicException::class);

        $this->allowedDatabaseColumnsProvider->getAllAllowedColumnsSetIndexedByTableNames();
    }

    #[Override]
    protected function setUpTestSchema(): void
    {
    }
}

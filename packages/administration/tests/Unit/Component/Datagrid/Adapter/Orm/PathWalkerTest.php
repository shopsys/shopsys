<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyDocument;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyTextType;

/**
 * How a path is joined into a query is covered by `ProxyQueryTest`; this asserts how the walk describes the path.
 */
final class PathWalkerTest extends TestCase
{
    /**
     * @return array<string, array{string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum}>
     */
    public static function pathDescriptionDataProvider(): array
    {
        return [
            'own field' => ['name', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING],
            'date field' => ['createdAt', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DATETIME],
            'field through a to-one association' => ['currency.code', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING],
            'identifier of a to-one association' => ['currency.id', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER],
            'to-one association itself' => ['currency', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::ASSOCIATION],
            'field through the inverse side of a one-to-one association' => ['detail.note', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING],
            'field through a to-many association' => ['domains.domainId', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::INTEGER],
            'to-many association itself' => ['tags', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::ASSOCIATION],
            'translated field through a to-many association' => ['labels.name', PathCardinalityEnum::TO_MANY, PathValueTypeEnum::STRING],
        ];
    }

    #[DataProvider('pathDescriptionDataProvider')]
    public function testPathIsDescribedByItsCardinalityAndValueType(
        string $path,
        PathCardinalityEnum $expectedCardinality,
        PathValueTypeEnum $expectedValueType,
    ): void {
        $pathDescription = $this->createPathWalker()->walk(DummyProduct::class, $path)->description;

        $this->assertSame($path, $pathDescription->path);
        $this->assertSame($expectedCardinality, $pathDescription->cardinality);
        $this->assertSame($expectedValueType, $pathDescription->valueType);
    }

    /**
     * An own type of a project extending a text type is text, a GUID is not, although it extends the string type —
     * a text function of the database refuses it.
     */
    public function testValueTypeOfDoctrineTypeIsReadFromItsClass(): void
    {
        if (Type::hasType(DummyTextType::NAME) === false) {
            Type::addType(DummyTextType::NAME, DummyTextType::class);
        }

        $pathWalker = $this->createPathWalker();

        $this->assertSame(PathValueTypeEnum::STRING, $pathWalker->walk(DummyDocument::class, 'title')->description->valueType);
        $this->assertSame(PathValueTypeEnum::UNKNOWN, $pathWalker->walk(DummyDocument::class, 'uuid')->description->valueType);
        $this->assertSame(PathValueTypeEnum::UNKNOWN, $pathWalker->walk(DummyDocument::class, 'payload')->description->valueType);
    }

    public function testUnknownPathIsRefused(): void
    {
        $this->expectException(PathNotFoundException::class);

        $this->createPathWalker()->walk(DummyProduct::class, 'currency.unknown');
    }

    private function createPathWalker(): PathWalker
    {
        $configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/Fixture'], true);

        // the server version is pinned so that the PostgreSQL platform is resolved without connecting to a database
        $connection = DriverManager::getConnection(
            [
                'driver' => 'pdo_pgsql',
                'serverVersion' => '16',
            ],
            $configuration,
        );

        return new PathWalker(new EntityManager($connection, $configuration));
    }
}

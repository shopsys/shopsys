<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Doctrine\DBAL\Types\Type;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\IntegerRange;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyCurrency;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyDocument;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyEntityManagerFactory;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyTag;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyTextType;

final class PathWalkerTest extends TestCase
{
    /**
     * @return array<string, array{\Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription}>
     */
    public static function pathDescriptionDataProvider(): array
    {
        $toOne = PathCardinalityEnum::TO_ONE;
        $toMany = PathCardinalityEnum::TO_MANY;
        $integerRange = new IntegerRange(-2147483648, 2147483647);

        return [
            'own field' => [new PathDescription('name', $toOne, PathValueTypeEnum::STRING)],
            'date field' => [new PathDescription('releasedOn', $toOne, PathValueTypeEnum::DATE)],
            'date and time field' => [new PathDescription('createdAt', $toOne, PathValueTypeEnum::DATETIME)],
            'field through a to-one association' => [new PathDescription('currency.code', $toOne, PathValueTypeEnum::STRING)],
            'identifier of a to-one association' => [new PathDescription('currency.id', $toOne, PathValueTypeEnum::INTEGER, null, $integerRange)],
            'field of an embeddable through a to-one association' => [new PathDescription('warehouse.address.country', $toOne, PathValueTypeEnum::STRING)],
            'to-one association itself' => [new PathDescription('currency', $toOne, PathValueTypeEnum::ASSOCIATION, DummyCurrency::class)],
            'field through the inverse side of a one-to-one association' => [new PathDescription('detail.note', $toOne, PathValueTypeEnum::STRING)],
            'field through a to-many association' => [new PathDescription('domains.domainId', $toMany, PathValueTypeEnum::INTEGER, null, $integerRange)],
            'to-many association itself' => [new PathDescription('tags', $toMany, PathValueTypeEnum::ASSOCIATION, DummyTag::class)],
            'translated field through a to-many association' => [new PathDescription('labels.name', $toMany, PathValueTypeEnum::STRING)],
        ];
    }

    #[DataProvider('pathDescriptionDataProvider')]
    public function testPathIsDescribedByWhatItLeadsTo(PathDescription $expectedPathDescription): void
    {
        $this->assertEquals(
            $expectedPathDescription,
            $this->createPathWalker()->walk(DummyProduct::class, $expectedPathDescription->path)->description,
        );
    }

    /**
     * @return array<string, array{string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum}>
     */
    public static function doctrineTypeDataProvider(): array
    {
        return [
            'own type extending the text type is STRING' => ['title', PathValueTypeEnum::STRING],
            'guid is GUID, although its type extends the string type' => ['uuid', PathValueTypeEnum::GUID],
            'json is UNKNOWN' => ['payload', PathValueTypeEnum::UNKNOWN],
        ];
    }

    #[DataProvider('doctrineTypeDataProvider')]
    public function testValueTypeIsReadFromDoctrineType(string $path, PathValueTypeEnum $expectedValueType): void
    {
        if (Type::hasType(DummyTextType::NAME) === false) {
            Type::addType(DummyTextType::NAME, DummyTextType::class);
        }

        $this->assertSame($expectedValueType, $this->createPathWalker()->walk(DummyDocument::class, $path)->description->valueType);
    }

    public function testUnknownPathIsRefused(): void
    {
        $this->expectException(PathNotFoundException::class);

        $this->createPathWalker()->walk(DummyProduct::class, 'currency.unknown');
    }

    public function testIntegerIsBoundedByTheRangeOfItsColumn(): void
    {
        $pathWalker = $this->createPathWalker();

        $smallIntegerDescription = $pathWalker->walk(DummyProduct::class, 'rating')->description;
        $integerDescription = $pathWalker->walk(DummyProduct::class, 'currency.id')->description;
        $bigIntegerDescription = $pathWalker->walk(DummyProduct::class, 'viewCount')->description;
        $textDescription = $pathWalker->walk(DummyProduct::class, 'name')->description;

        $this->assertEquals(new IntegerRange(-32768, 32767), $smallIntegerDescription->integerRange);
        $this->assertEquals(new IntegerRange(-2147483648, 2147483647), $integerDescription->integerRange);
        $this->assertNull($bigIntegerDescription->integerRange);
        $this->assertNull($textDescription->integerRange);
    }

    /**
     * An embeddable is no value to compare, only its fields are.
     */
    public function testPathEndingWithEmbeddableIsRefused(): void
    {
        $this->expectException(PathNotFoundException::class);
        $this->expectExceptionMessage('has no field or association "address"');

        $this->createPathWalker()->walk(DummyProduct::class, 'warehouse.address');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function getPathsThroughCompositeIdentifier(): iterable
    {
        yield 'field of the identifier' => ['shelf.number'];

        yield 'another field of the entity' => ['shelf.label'];

        yield 'the association itself' => ['shelf'];
    }

    /**
     * DQL cannot compare the association with the joined entity, and IDENTITY() would read only the first column
     * of the identifier.
     */
    #[DataProvider('getPathsThroughCompositeIdentifier')]
    public function testAssociationToEntityIdentifiedBySeveralFieldsIsRefused(string $path): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('identified by several fields');

        $this->createPathWalker()->walk(DummyProduct::class, $path);
    }

    private function createPathWalker(): PathWalker
    {
        return new PathWalker(DummyEntityManagerFactory::create());
    }
}

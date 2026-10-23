<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm;

use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathStepKind;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyDocument;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyEntityManagerFactory;
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
            'field of an identifier of several fields' => ['shelf.number', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER],
            'field through an association to an entity identified by several fields' => ['shelf.label', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING],
            'field of an embeddable through a to-one association' => ['warehouse.address.country', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING],
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

    /**
     * A filter holds an entered integer within the range of its column, beyond which the query would fail.
     */
    public function testIntegerIsBoundedByTheRangeOfItsColumn(): void
    {
        $pathWalker = $this->createPathWalker();

        $smallIntegerDescription = $pathWalker->walk(DummyProduct::class, 'rating')->description;
        $integerDescription = $pathWalker->walk(DummyProduct::class, 'currency.id')->description;
        $bigIntegerDescription = $pathWalker->walk(DummyProduct::class, 'viewCount')->description;
        $textDescription = $pathWalker->walk(DummyProduct::class, 'name')->description;

        $this->assertSame([-32768, 32767], [$smallIntegerDescription->minimumValue, $smallIntegerDescription->maximumValue]);
        $this->assertSame([-2147483648, 2147483647], [$integerDescription->minimumValue, $integerDescription->maximumValue]);
        $this->assertSame([null, null], [$bigIntegerDescription->minimumValue, $bigIntegerDescription->maximumValue]);
        $this->assertSame([null, null], [$textDescription->minimumValue, $textDescription->maximumValue]);
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
     * The identifier of an association is read without a join by the field only when it spans several fields —
     * IDENTITY() reads the first column otherwise.
     */
    public function testIdentifierOfAssociationIsReadByTheFieldOnlyWhenItSpansSeveralFields(): void
    {
        $pathWalker = $this->createPathWalker();

        $severalFieldsStep = $pathWalker->walk(DummyProduct::class, 'shelf.number')->steps[0];
        $singleFieldStep = $pathWalker->walk(DummyProduct::class, 'currency.id')->steps[0];

        $this->assertSame(PathStepKind::IDENTITY, $severalFieldsStep->kind);
        $this->assertSame('number', $severalFieldsStep->identifierField);
        $this->assertSame(PathStepKind::IDENTITY, $singleFieldStep->kind);
        $this->assertNull($singleFieldStep->identifierField);
    }

    private function createPathWalker(): PathWalker
    {
        return new PathWalker(DummyEntityManagerFactory::create());
    }
}

<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\InvalidDatagridDeclarationException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\Searchable;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchablePathResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchedPath;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class SearchablePathResolverTest extends TestCase
{
    public function testOnlySearchableFieldsAreSearchedIn(): void
    {
        $searchedPaths = $this->createResolver()->resolve($this->createAdapter(), [
            new FieldDescriptor('name', ['label' => 'Name', 'searchable' => true]),
            new FieldDescriptor('brandName', ['label' => 'Brand', 'property' => 'brand.name', 'searchable' => true]),
            new FieldDescriptor('note', ['label' => 'Note']),
        ]);

        $this->assertEquals([
            new SearchedPath('name', 'Name', PathValueTypeEnum::STRING),
            new SearchedPath('brand.name', 'Brand', PathValueTypeEnum::STRING),
        ], $searchedPaths);
    }

    public function testNothingIsSearchedInWithoutSearchableField(): void
    {
        $this->assertSame([], $this->createResolver()->resolve($this->createAdapter(), [new FieldDescriptor('name')]));
    }

    public function testVirtualFieldWithoutPathIsRefused(): void
    {
        $this->expectException(InvalidDatagridDeclarationException::class);
        $this->expectExceptionMessage('Field "computed" cannot be searchable: a virtual field has no path');

        $this->createResolver()->resolve($this->createAdapter(), [
            new FieldDescriptor('computed', ['searchable' => true, 'virtual' => true]),
        ]);
    }

    /**
     * An adapter without a schema cannot be asked, so the path is checked only when the expression is built.
     */
    public function testEveryPathIsSearchedInAsTextOverAdapterWithoutSchema(): void
    {
        $adapterStub = $this->createStub(AdapterInterface::class);

        $searchedPaths = $this->createResolver()->resolve($adapterStub, [
            new FieldDescriptor('product', ['label' => 'Product', 'property' => ['productName', 'product.id'], 'searchable' => true]),
        ]);

        $this->assertEquals([
            new SearchedPath('productName', 'Product', null),
            new SearchedPath('product.id', 'Product', null),
        ], $searchedPaths);
    }

    public function testFieldLeadingToNonTextValueIsRefused(): void
    {
        $adapter = $this->createAdapter([
            'createdAt' => new PathDescription('createdAt', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DATETIME),
        ]);

        $this->expectException(InvalidDatagridDeclarationException::class);
        $this->expectExceptionMessage('Field "createdAt" cannot be searchable: Operator "contains" cannot be applied to path "createdAt": text can only be searched in a text value, the path leads to DATETIME.');

        $this->createResolver()->resolve($adapter, [new FieldDescriptor('createdAt', ['searchable' => true])]);
    }

    public function testUnknownPathIsRefused(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), [], false);

        $this->expectException(InvalidDatagridDeclarationException::class);
        $this->expectExceptionMessage('Field "unknown" cannot be searchable: Path "unknown" cannot be resolved');

        $this->createResolver()->resolve($adapter, [new FieldDescriptor('unknown', ['searchable' => true])]);
    }

    public function testUuidIsSearchedIn(): void
    {
        $adapter = $this->createAdapter([
            'uuid' => new PathDescription('uuid', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::GUID),
        ]);

        $searchedPaths = $this->createResolver()->resolve($adapter, [
            new FieldDescriptor('uuid', ['label' => 'UUID', 'searchable' => true]),
        ]);

        $this->assertEquals([new SearchedPath('uuid', 'UUID', PathValueTypeEnum::GUID)], $searchedPaths);
    }

    /**
     * `true` searches in every property, so a property that is neither text nor a UUID is not skipped silently.
     */
    public function testCombinedFieldWithPropertyLeadingToNonTextValueIsRefused(): void
    {
        $adapter = $this->createAdapter([
            'product.id' => new PathDescription('product.id', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER),
        ]);

        $this->expectException(InvalidDatagridDeclarationException::class);
        $this->expectExceptionMessage('new Searchable(paths: [...])');

        $this->createResolver()->resolve($adapter, [
            new FieldDescriptor('product', ['property' => ['productName', 'catnum', 'product.id'], 'searchable' => true]),
        ]);
    }

    public function testPickedPathsAreSearchedInUnderOwnLabel(): void
    {
        $adapter = $this->createAdapter([
            'product.id' => new PathDescription('product.id', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER),
        ]);

        $searchedPaths = $this->createResolver()->resolve($adapter, [
            new FieldDescriptor('product', [
                'label' => 'Product',
                'property' => ['productName', 'catnum', 'product.id'],
                'searchable' => new Searchable(paths: ['productName', 'catnum'], label: 'Product name or catalog number'),
            ]),
        ]);

        $this->assertEquals([
            new SearchedPath('productName', 'Product name or catalog number', PathValueTypeEnum::STRING),
            new SearchedPath('catnum', 'Product name or catalog number', PathValueTypeEnum::STRING),
        ], $searchedPaths);
    }

    public function testPickedPathLeadingToNonTextValueIsRefused(): void
    {
        $adapter = $this->createAdapter([
            'product.id' => new PathDescription('product.id', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER),
        ]);

        $this->expectException(InvalidDatagridDeclarationException::class);
        $this->expectExceptionMessage('Field "product" cannot be searchable: Operator "contains" cannot be applied to path "product.id"');

        $this->createResolver()->resolve($adapter, [
            new FieldDescriptor('product', [
                'property' => ['productName', 'product.id'],
                'searchable' => new Searchable(paths: ['product.id']),
            ]),
        ]);
    }

    /**
     * The administrator may see the properties of the field, while another column may belong to a field hidden
     * from them.
     */
    public function testPathOutsideThePropertiesOfTheFieldIsRefused(): void
    {
        $this->expectException(InvalidDatagridDeclarationException::class);
        $this->expectExceptionMessage('"purchasePrice" is none of them');

        $this->createResolver()->resolve($this->createAdapter(), [
            new FieldDescriptor('name', ['searchable' => new Searchable(paths: ['name', 'purchasePrice'])]),
        ]);
    }

    private function createResolver(): SearchablePathResolver
    {
        return new SearchablePathResolver(new ExpressionOperatorEnum());
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription> $pathDescriptions Every other path leads to a single text value
     */
    private function createAdapter(array $pathDescriptions = []): CapturingAdapter
    {
        return new CapturingAdapter($this->createStub(DataSourceInterface::class), $pathDescriptions);
    }
}

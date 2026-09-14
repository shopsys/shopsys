<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchablePathResolver;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class SearchablePathResolverTest extends TestCase
{
    public function testOnlySearchableFieldsAreSearchedIn(): void
    {
        $labelsByPath = $this->createResolver()->resolve($this->createAdapter(), [
            new FieldDescriptor('name', ['label' => 'Name', 'searchable' => true]),
            new FieldDescriptor('brandName', ['label' => 'Brand', 'property' => 'brand.name', 'searchable' => true]),
            new FieldDescriptor('note', ['label' => 'Note']),
        ]);

        $this->assertSame(['name' => 'Name', 'brand.name' => 'Brand'], $labelsByPath);
    }

    public function testNothingIsSearchedInWithoutSearchableField(): void
    {
        $this->assertSame([], $this->createResolver()->resolve($this->createAdapter(), [new FieldDescriptor('name')]));
    }

    public function testVirtualFieldWithoutPathIsRefused(): void
    {
        $this->expectException(FieldNotSearchableException::class);

        $this->createResolver()->resolve($this->createAdapter(), [
            new FieldDescriptor('computed', ['searchable' => true, 'virtual' => true]),
        ]);
    }

    /**
     * An adapter without a schema cannot be asked, so the path is checked only when the expression is built.
     */
    public function testEveryPathIsSearchedInOverAdapterWithoutSchema(): void
    {
        $adapterStub = $this->createStub(AdapterInterface::class);

        $labelsByPath = $this->createResolver()->resolve($adapterStub, [
            new FieldDescriptor('product', ['label' => 'Product', 'property' => ['productName', 'product.id'], 'searchable' => true]),
        ]);

        $this->assertSame(['productName' => 'Product', 'product.id' => 'Product'], $labelsByPath);
    }

    public function testFieldLeadingToNonTextValueIsRefused(): void
    {
        $adapter = $this->createAdapter([
            'createdAt' => new PathDescription('createdAt', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DATETIME),
        ]);

        $this->expectException(FieldNotSearchableException::class);
        $this->expectExceptionMessage('createdAt');

        $this->createResolver()->resolve($adapter, [new FieldDescriptor('createdAt', ['searchable' => true])]);
    }

    /**
     * A combined field links by an identifier its template needs, which is no text to search in.
     */
    public function testCombinedFieldIsSearchedInItsTextPropertiesOnly(): void
    {
        $adapter = $this->createAdapter([
            'product.id' => new PathDescription('product.id', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER),
        ]);

        $labelsByPath = $this->createResolver()->resolve($adapter, [
            new FieldDescriptor('product', ['label' => 'Product', 'property' => ['productName', 'catnum', 'product.id'], 'searchable' => true]),
        ]);

        $this->assertSame(['productName' => 'Product', 'catnum' => 'Product'], $labelsByPath);
    }

    public function testCombinedFieldWithoutTextPropertyIsRefused(): void
    {
        $adapter = $this->createAdapter([
            'product.id' => new PathDescription('product.id', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::INTEGER),
            'createdAt' => new PathDescription('createdAt', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DATETIME),
        ]);

        $this->expectException(FieldNotSearchableException::class);

        $this->createResolver()->resolve($adapter, [
            new FieldDescriptor('product', ['property' => ['product.id', 'createdAt'], 'searchable' => true]),
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

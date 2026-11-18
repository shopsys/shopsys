<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Field;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\Searchable;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class FieldDescriptorTest extends TestCase
{
    /**
     * @param string[] $expectedSelectProperties
     */
    #[DataProvider('propertiesDataProvider')]
    public function testProperties(
        array $options,
        array $expectedSelectProperties,
        ?string $expectedMappingProperty,
        bool $expectedSortable,
    ): void {
        $fieldDescriptor = new FieldDescriptor('name', $options);

        $this->assertSame($expectedSelectProperties, $fieldDescriptor->getSelectProperties());
        $this->assertSame($expectedMappingProperty, $fieldDescriptor->getMappingProperty());
        $this->assertSame($expectedSortable, $fieldDescriptor->isSortable());
    }

    /**
     * @return array<string, array{options: mixed[], expectedSelectProperties: string[], expectedMappingProperty: string|null, expectedSortable: bool}>
     */
    public static function propertiesDataProvider(): array
    {
        $transform = static fn (mixed $value): mixed => $value;

        return [
            'field name is used as the property' => [
                'options' => [],
                'expectedSelectProperties' => ['name'],
                'expectedMappingProperty' => 'name',
                'expectedSortable' => true,
            ],
            'single property' => [
                'options' => ['property' => 'customer.name'],
                'expectedSelectProperties' => ['customer.name'],
                'expectedMappingProperty' => 'customer.name',
                'expectedSortable' => true,
            ],
            'multiple properties are selected and the combined value is read from the field name' => [
                'options' => ['property' => ['lastName', 'firstName']],
                'expectedSelectProperties' => ['lastName', 'firstName'],
                'expectedMappingProperty' => 'name',
                'expectedSortable' => true,
            ],
            'transformed value is read from the field name and is not sortable' => [
                'options' => ['property' => 'id', 'transform' => $transform],
                'expectedSelectProperties' => ['id'],
                'expectedMappingProperty' => 'name',
                'expectedSortable' => false,
            ],
            'virtual field with property is not selected but is sortable' => [
                'options' => ['virtual' => true, 'property' => 'computedAlias'],
                'expectedSelectProperties' => [],
                'expectedMappingProperty' => 'computedAlias',
                'expectedSortable' => true,
            ],
            'virtual field without property has no value' => [
                'options' => ['virtual' => true],
                'expectedSelectProperties' => [],
                'expectedMappingProperty' => null,
                'expectedSortable' => false,
            ],
        ];
    }

    public function testFieldIsNotRestrictedByDefault(): void
    {
        $fieldDescriptor = new FieldDescriptor('name');

        $this->assertFalse($fieldDescriptor->isRestricted());
        $this->assertNull($fieldDescriptor->getRole());
        $this->assertNull($fieldDescriptor->getPermission());
        $this->assertNull($fieldDescriptor->getClass());
    }

    public function testFieldIsRestrictedByRoleOrPermission(): void
    {
        $this->assertTrue(new FieldDescriptor('name', ['role' => 'ROLE_PRODUCT'])->isRestricted());
        $this->assertTrue(new FieldDescriptor('name', ['permission' => Permission::EDIT])->isRestricted());
    }

    public function testEmptyPropertyArrayIsNotAllowed(): void
    {
        $this->expectException(InvalidOptionsException::class);

        new FieldDescriptor('name', ['property' => []]);
    }

    /**
     * @param mixed[] $options
     */
    #[DataProvider('searchableDataProvider')]
    public function testSearchableOptionSaysWhereTheFieldIsSearchedIn(
        array $options,
        ?Searchable $expectedSearchable,
    ): void {
        $this->assertEquals($expectedSearchable, new FieldDescriptor('reviewer', $options)->getSearchable());
    }

    /**
     * @return iterable<string, array{mixed[], \Shopsys\AdministrationBundle\Component\Datagrid\Search\Searchable|null}>
     */
    public static function searchableDataProvider(): iterable
    {
        yield 'not searched in by default' => [[], null];

        yield 'not searched in when switched off' => [['searchable' => false], null];

        yield 'true searches in every property under the label of the field' => [['searchable' => true], new Searchable()];

        yield 'searchable picks the properties and the label' => [
            ['property' => ['lastName', 'firstName', 'customerUser.id'], 'searchable' => new Searchable(paths: ['lastName', 'firstName'], label: 'Reviewer name')],
            new Searchable(paths: ['lastName', 'firstName'], label: 'Reviewer name'),
        ];
    }

    public function testSearchableCanBeSwitchedOnByUpdate(): void
    {
        $field = new FieldDescriptor('name');

        $field->update(['searchable' => true]);

        $this->assertEquals(new Searchable(), $field->getSearchable());
    }

    public function testSearchableIsRefusedWhenNeitherBooleanNorSearchable(): void
    {
        $this->expectException(InvalidOptionsException::class);

        /** @phpstan-ignore argument.type */
        new FieldDescriptor('name', ['searchable' => 'yes']);
    }

    public function testFieldIsNotFilterableByDefault(): void
    {
        $this->assertFalse((new FieldDescriptor('name'))->isFilterable());
    }

    public function testFilterableCanBeSwitchedOnByUpdate(): void
    {
        $field = new FieldDescriptor('name');

        $field->update(['filterable' => true]);

        $this->assertTrue($field->isFilterable());
    }

    public function testFilterNameIsTheFieldNameWithoutDots(): void
    {
        $this->assertSame('status', (new FieldDescriptor('status'))->getFilterName());
        $this->assertSame('customerUser_email', (new FieldDescriptor('customerUser.email'))->getFilterName());
    }
}

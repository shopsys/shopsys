<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Field;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

final class FieldDescriptorTest extends TestCase
{
    public function testFieldIsNotSearchableByDefault(): void
    {
        $this->assertFalse((new FieldDescriptor('name'))->isSearchable());
    }

    public function testSearchableFieldKeepsItsPathAsTheSearchedOne(): void
    {
        $field = new FieldDescriptor('brandName', [
            'searchable' => true,
            'property' => 'brand.name',
        ]);

        $this->assertTrue($field->isSearchable());
        $this->assertSame('brand.name', $field->getSelectProperty());
    }

    public function testSearchableCanBeSwitchedOnByUpdate(): void
    {
        $field = new FieldDescriptor('name');

        $field->update(['searchable' => true]);

        $this->assertTrue($field->isSearchable());
    }

    public function testSearchableIsRefusedWhenNotBoolean(): void
    {
        $this->expectException(InvalidOptionsException::class);

        // the static analysis refuses this as well, which is the first line of defence
        /** @phpstan-ignore argument.type */
        new FieldDescriptor('name', ['searchable' => 'yes']);
    }
}

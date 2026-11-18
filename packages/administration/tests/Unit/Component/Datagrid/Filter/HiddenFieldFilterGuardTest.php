<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\HiddenFieldFilterGuard;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;

final class HiddenFieldFilterGuardTest extends TestCase
{
    /**
     * @return iterable<string, array{hiddenField: \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor, filter: \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface}>
     */
    public static function getFiltersTellingWhatHiddenFieldHolds(): iterable
    {
        yield 'filter under the filter name of the field' => [
            'hiddenField' => new FieldDescriptor('customerUser.email'),
            'filter' => TextFilter::new('email')->setName('customerUser_email'),
        ];

        yield 'filter over the property of the field' => [
            'hiddenField' => new FieldDescriptor('cost', ['property' => 'purchasePrice']),
            'filter' => TextFilter::new('purchasePrice'),
        ];

        yield 'filter over one of the properties of the field' => [
            'hiddenField' => new FieldDescriptor('customer', ['property' => ['firstName', 'lastName']]),
            'filter' => TextFilter::new('lastName'),
        ];

        yield 'filter under the property' => [
            'hiddenField' => new FieldDescriptor('customerUser'),
            'filter' => TextFilter::new('customerUser.email'),
        ];

        yield 'filter above the property' => [
            'hiddenField' => new FieldDescriptor('street', ['property' => 'address.street']),
            'filter' => TextFilter::new('address'),
        ];

        yield 'translations of a translated field' => self::createTranslationsCase('name', 'translations.name');

        yield 'translated field of the translations' => self::createTranslationsCase('translations.name', 'name');

        yield 'translations of a translated field of an association' => self::createTranslationsCase('status.name', 'status.translations.name');

        yield 'field under the association of the translations' => self::createTranslationsCase('translations', 'translations.name');

        yield 'translations of an association above its translated field' => self::createTranslationsCase('status.name', 'status.translations');

        yield 'translations of the listed entity above its translated field' => self::createTranslationsCase('name', 'translations');
    }

    #[DataProvider('getFiltersTellingWhatHiddenFieldHolds')]
    public function testFilterTellingWhatHiddenFieldHoldsIsHidden(
        FieldDescriptor $hiddenField,
        FilterInterface $filter,
    ): void {
        $this->assertTrue((new HiddenFieldFilterGuard([$hiddenField]))->hides($filter));
    }

    /**
     * @return iterable<string, array{hiddenField: \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor, filter: \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface}>
     */
    public static function getFiltersNotTellingWhatHiddenFieldHolds(): iterable
    {
        yield 'filter over another branch of the association' => [
            'hiddenField' => new FieldDescriptor('street', ['property' => 'address.street']),
            'filter' => TextFilter::new('address.city'),
        ];

        yield 'filter over a path starting with the name of the property' => [
            'hiddenField' => new FieldDescriptor('customerUser'),
            'filter' => TextFilter::new('customerUserGroup'),
        ];

        yield 'another translated field of the association' => self::createTranslationsCase('status.name', 'status.translations.description');

        yield 'untranslated field of the association owning the translations' => self::createTranslationsCase('status.translations', 'status.code');
    }

    #[DataProvider('getFiltersNotTellingWhatHiddenFieldHolds')]
    public function testFilterNotTellingWhatHiddenFieldHoldsIsNotHidden(
        FieldDescriptor $hiddenField,
        FilterInterface $filter,
    ): void {
        $this->assertFalse((new HiddenFieldFilterGuard([$hiddenField]))->hides($filter));
    }

    /**
     * @return array{hiddenField: \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor, filter: \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface}
     */
    private static function createTranslationsCase(string $hiddenProperty, string $filterPath): array
    {
        return [
            'hiddenField' => new FieldDescriptor('hidden', ['property' => $hiddenProperty]),
            'filter' => TextFilter::new($filterPath),
        ];
    }
}

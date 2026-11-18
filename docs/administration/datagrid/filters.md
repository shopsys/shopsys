# Filters

A filter lets the administrator compose rules over the listed records — "status is one of pending, approved", "rating is greater than 3", "product is the one picked in the window" — grouped and combined by `AND` or `OR`.
The rules travel in the URL, so a filtered list can be shared by its link.

## Quick start

```php
$datagrid
    ->add('createdAt', ['label' => t('Created'), 'filterable' => true])             // a DateFilter is picked
    ->add('totalPriceWithVat', ['label' => t('Total price'), 'filterable' => true]); // a MoneyFilter is picked

$datagrid->filters()
    ->add(TextFilter::new('customerUser.email', t('Customer e-mail')))
    ->add(EntityFilter::new('status', t('Status')));
```

A field declared `'filterable' => true` gets a filter picked by the value its path leads to. `$datagrid->filters()->add()` declares a filter over any path of the records.

## Filters of the fields

| The path of the field leads to | Filter |
|---|---|
| text | `TextFilter` |
| an integer or a decimal number | `NumericFilter` |
| money | `MoneyFilter` |
| a date, a date and time | `DateFilter` |
| a boolean | `BooleanFilter` |
| a product | `ProductFilter` |

The filter takes the label of the field. Its name in the URL is the name of the field with the dots replaced by underscores (`customerUser.email` → `customerUser_email`).

`InvalidDatagridDeclarationException` is thrown when the state of the datagrid is created for:

- a virtual field and a field combining several properties — there is no single path to filter by,
- a path that does not exist, or one the adapter cannot describe (`ArrayAdapter`),
- any other value — a UUID, an unknown type, an association to another entity than a product.

Declare the filter of such a field by `filters()->add()`, for example an [`EntityFilter`](#the-built-in-filters) for an association to a small table.

The `filterable` option puts a `GuessedFilter` into `$datagrid->filters()` under the name of the filter of the field. The filter itself is picked only when the state of the datagrid is created. Therefore:

- `$datagrid->filters()->remove('rating')` removes the filter of a field, whether it was picked or declared. `$datagrid->update('rating', ['filterable' => false])` removes only a picked one. An extension switches the filter on by `update()` with `'filterable' => true`.
- A filter declared under the same name replaces it, whether it is declared before the field or after it. That is how a field gets a filter configured in detail.
- `filters()->get('rating')->setOperators()` cannot configure a picked filter — declare the filter instead:

```php
$datagrid->add('rating', ['label' => t('Rating'), 'filterable' => true]);

// replaces the NumericFilter picked for the field and offers two of its operations only
$datagrid->filters()->add(
    NumericFilter::new('rating', t('Rating'))->setOperators([ExpressionOperatorEnum::EQUALS, ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL]),
);
```

To pick the filters differently in a project, replace the `FilterGuesser` service and override its `createFilter()`.

## Declaring filters

```php
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ChoiceFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\DateFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\EntityFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\MoneyFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ProductFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;

$datagrid->filters()
    // 'author' is an association of the Book entity, the path is the same dot notation the fields use
    ->add(TextFilter::new('author.fullName', t('Author name')))
    // 'address' is a Doctrine embeddable inside the Author entity, its fields are addressed through it
    ->add(TextFilter::new('author.address.country', t('Author country')))
    // the path of an EntityFilter ends with an association, every publisher is offered in a select
    ->add(EntityFilter::new('author.publisher', t('Publisher')))
    ->add(DateFilter::new('createdAt', t('Created')))
    ->add(NumericFilter::new('rating', t('Rating')))
    ->add(MoneyFilter::new('price', t('Price')))
    ->add(ChoiceFilter::new('status', t('Status'))->setChoices($this->statusEnum->getAllIndexedByTranslations()))
    ->add(BooleanFilter::new('isVerifiedPurchase', t('Verified purchase')))
    ->add(ProductFilter::new('product', t('Product')));
```

Every filter takes the path and an optional label, derived from the last part of the path otherwise. It offers:

- `setName()` — the key of the filter in the URL, derived from the path (`author_fullName`) by default. Set it when two filters share a path. Keep it stable, a renamed filter breaks bookmarked links.
- `setOperators()` — a subset of the operations the filter offers, in the order they are offered. Any other operation is refused.
- `setValueFormOptions()` — options merged into the value form type (`choices`, `query_builder`, `attr`, ...).

Two filters of the same name throw an exception. To replace a declared filter in an extension, remove it first: `$datagrid->filters()->remove('status')->add(...)`.

The filters are offered in the alphabetical order of their labels in the language of the administration, wherever they were declared.

## The built-in filters

| Filter | The path leads to | Operations | Value |
|---|---|---|---|
| `TextFilter` | text | contains, starts with, ends with, is, is not, is (not) filled | text |
| `NumericFilter` | an integer or a decimal number | is, is not, greater / less than (or equal), between, is (not) filled | integer or decimal by the path, an integer within the range of its column |
| `MoneyFilter` | money | is, is not, greater / less than (or equal), between, is (not) filled | an amount with up to six decimal places, without a currency |
| `DateFilter` | a date, a date and time | is, is before, is after, between, is (not) filled | a day from the date picker |
| `BooleanFilter` | a boolean | yes, no | none, the choice is the operation itself |
| `ChoiceFilter` | any value | is one of, is none of, is, is not | one or several of `setChoices()` |
| `EntityFilter` | an association | is one of, is none of, is (not) filled; has none / has some on a to-many path | related entities in a select, compared by their identifier |
| `ProductFilter` | an association to a product | is, is not | a product picked in the product picker window |

The operations of a filter are narrowed to those applicable to its path, so a text search is never offered on a number. `InvalidDatagridDeclarationException` is thrown when the state of the datagrid is created for a filter whose path does not exist, leads to another kind of value (a `NumericFilter` on money, an `EntityFilter` on a property) or keeps no operation. An adapter that cannot describe its paths takes the declaration as it is, see [Differences between the adapters](./narrowing.md#differences-between-the-adapters).

`MoneyFilter` compares the amount regardless of the currency. Narrow a list with prices in several currencies to one of them first, by a domain for example.

`EntityFilter` offers every row of the related entity in a select, loaded whenever the list is displayed. Declare it for a small table (a status, a unit, a flag); the catalogue is filtered by `ProductFilter` and its picker. The select ignores the domain of the list. For an entity that depends on the domain, limit the offer by `setValueFormOptions(['query_builder' => ...])` or by overriding `createChoicesQueryBuilder()`. The limit narrows the accepted values as well. Your own `query_builder` replaces the default query, the join of the translations included.

`DateFilter` over a date and time treats the picked day as a range in the time zone of the administration: "is 12.5." means from its midnight to the last moment before the next midnight, "is before 12.5." means before its midnight, "is after 12.5." means from the next midnight. A date without time is compared as it is.

Over a collection, "is not" and "is none of" negate the whole condition — "tags is none of Sale" lists the records without the tag Sale, see [Paths and collections](./narrowing.md#paths-and-collections).

## What the administrator sees

- The form starts with one group holding one empty rule. Each group combines its rules by `AND` or `OR`, and the groups are combined by `AND` or `OR` as well.
- A rule without a value narrows nothing. A range with only one of its bounds filled is an error.
- The quick search and the filter share one card as tabs. Only one of them applies: the filter whenever a rule is composed, the quick search otherwise. Submitting one form drops the parameters of the other.
- The domain control moves into a card beside them as a compact select.
- An invalid filter narrows nothing and shows its errors, and the page answers 422. A filter is invalid when a value is malformed, an operation is not offered, or it has more than 10 groups or more than 10 rules in a group.

A datagrid outside the CRUD controller renders the filter by the same component, see [A datagrid outside the CRUD controller](./narrowing.md#a-datagrid-outside-the-crud-controller).

## The advanced search of the older lists

The older lists of orders, products, customers and complaints keep their advanced search (`Shopsys\FrameworkBundle\Model\AdvancedSearch`). The filters described here serve the lists built on the datagrid, such as the lists of CRUD controllers. The quick search of a datagrid takes the text literally, while the quick search of the older lists reads `*` and `?` as wildcards.

## Writing your own filter

Filters are not services, so they are extended by inheritance — `class MyFilter extends ChoiceFilter`. `ChoiceFilter::new()` always creates the filter it names, the [static service access](../../extensibility/static-service-access.md) does not apply to filters.

A filter the platform does not offer — "created in the last N days":

```php
namespace App\Component\Datagrid\Filter;

use DateTimeImmutable;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\AbstractFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Positive;

class LastDaysFilter extends AbstractFilter
{
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL];
    }

    #[Override]
    public function getOperatorLabel(string $operator): string
    {
        return t('in the last (days)');
    }

    #[Override]
    public function getValueFormType(ExpressionValueArityEnum $arity): string
    {
        return IntegerType::class;
    }

    #[Override]
    protected function getDefaultValueFormOptions(ExpressionValueArityEnum $arity): array
    {
        return ['constraints' => [new Positive()]];
    }

    #[Override]
    protected function getApplicableValueTypes(): array
    {
        return [PathValueTypeEnum::DATE, PathValueTypeEnum::DATETIME];
    }

    #[Override]
    protected function createComparison(string $operator, mixed $value): ?ConditionInterface
    {
        // "in the last 7 days" counts today as one of them
        $since = new DateTimeImmutable('today', $this->getEnvironment()->displayTimeZone)->modify(sprintf('-%d days', $value - 1));

        return Condition::greaterThanOrEqual($this->path, $since);
    }
}
```

```php
// a second filter over the path of another filter needs a name of its own
$datagrid->filters()->add(LastDaysFilter::new('createdAt', t('Created recently'))->setName('createdRecently'));
```

The methods of `AbstractFilter` a filter overrides:

- `getDefaultOperators()` — the operations of the [vocabulary](./narrowing.md#the-vocabulary) the filter offers, in the order the administrator sees them.
- `getOperatorLabel()` — how an operation reads; the label of `ExpressionOperatorEnum` otherwise.
- `getValueFormType()` and `getDefaultValueFormOptions()` — the form type of the value and its options, for the arity of the operation (`ExpressionValueArityEnum::SINGLE`, `LIST`, or `RANGE` for one of the bounds). The default is a text input. The options of `setValueFormOptions()` are merged on top. `getEnvironment()` gives the time zone of the administration, `$this->pathDescription` describes the path (null when the adapter cannot describe it).
- `getApplicableValueTypes()` — the kinds of value the filter works with; a declaration over any other is refused when the state of the datagrid is created. Override `assertApplicable()` for another requirement (`ProductFilter` requires a product) and `isOperatorApplicable()` for a single operation.
- `createComparison()` — turns the operation and the value (a single value, a list, or `[from, to]` for a range) into a condition of the [condition tree](./narrowing.md#the-condition-tree). Returning null narrows nothing. What the vocabulary cannot express is a [`DqlCondition`](#a-condition-in-dql).

Compare a range by one `between`, never by two comparisons of its bounds — over a collection each comparison is matched by a related row of its own.

A filter is a plain object, so its unit test needs neither Doctrine nor a request: call `resolveFor()` with a `FilterEnvironment` over an adapter describing the path, then assert the operations and the condition `buildCondition()` builds from a `FilterRuleData`. `DateFilterTest` in `packages/administration/tests/Unit/Component/Datagrid/Filter/` shows the setup. A functional test requests the list with the rules in the URL — the form is named after the datagrid (`ProductReview_filter` for the product reviews), see `FilterListTest` in the project.

### A condition in DQL

Every adapter has to understand every operation of the [vocabulary](./narrowing.md#the-vocabulary). A one-off condition over the ORM adapter that the operations cannot express — an aggregate of the related rows, a computed value — is a `DqlCondition` returned by `createComparison()`. Orders with more items than the given number:

```php
namespace App\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Condition\DqlCondition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\AbstractFilter;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ItemCountFilter extends AbstractFilter
{
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [ExpressionOperatorEnum::GREATER_THAN];
    }

    #[Override]
    public function getValueFormType(ExpressionValueArityEnum $arity): string
    {
        return IntegerType::class;
    }

    #[Override]
    protected function createComparison(string $operator, mixed $value): ?ConditionInterface
    {
        // the path is a collection of the listed entity: SIZE(o.items) > :dgexpr_0
        return new DqlCondition(fn (ProxyQuery $proxyQuery): string => sprintf(
            'SIZE(%s.%s) > :%s',
            ProxyQuery::DEFAULT_ALIAS,
            $this->path,
            $proxyQuery->addParameter($value),
        ));
    }
}
```

```php
$datagrid->filters()->add(ItemCountFilter::new('items', t('Number of items')));
```

- The closure returns a predicate of the `WHERE` clause and composes with the rest of the tree.
- The datagrid then works with the ORM adapter only. Any other adapter throws `UnsupportedConditionException`.
- `ProxyQuery::addParameter()` registers a value and returns the name of its parameter.
- `ProxyQuery::resolvePath($path)->dqlExpression` is the DQL of a path, joined the same way the datagrid joins it. A path through a collection is never joined — `ProxyQuery::exists($path, $buildPredicate)` builds an `EXISTS` subquery instead.
- The root entity is aliased `ProxyQuery::DEFAULT_ALIAS` (`o`).

# Filters

A filter lets the administrator compose rules over the listed records — "status is one of pending, approved",
"rating is greater than 3", "product is the one picked in the window" — grouped and combined by `AND` or `OR`.
The filters are declared on the datagrid, the form is rendered above the records, and the composed rules
travel in the URL, so a filtered list can be shared by its link.

## Declaring filters

```php
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ChoiceFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\DateFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\EntityFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ProductFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;

protected function configureDatagrid(Datagrid $datagrid): void
{
    // ... fields

    $datagrid->filters()
        // 'author' is an association of the Book entity, the path is the same dot notation the fields use
        ->add(TextFilter::new('author.fullName', t('Author name')))
        // 'address' is a Doctrine embeddable inside the Author entity
        ->add(TextFilter::new('author.address.country', t('Author country')))
        // an EntityFilter path ends with an association instead of a property
        ->add(EntityFilter::new('author.publisher', t('Publisher')))
        ->add(DateFilter::new('createdAt', t('Created')))
        ->add(NumericFilter::new('rating', t('Rating')))
        ->add(ChoiceFilter::new('status', t('Status'))->setChoices($this->statusEnum->getAllIndexedByTranslations()))
        ->add(BooleanFilter::new('isVerifiedPurchase', t('Verified purchase')))
        ->add(ProductFilter::new('product', t('Product')));
}
```

Every filter takes the path and an optional label (derived from the last part of the path otherwise) and offers:

- `setName()` — the key of the filter in the URL, derived from the path (`author_fullName`) by default; keep it stable, a renamed filter breaks bookmarked links,
- `setOperators()` — a subset of the operations the type offers, in the order they are offered,
- `setValueFormOptions()` — options merged into the value form type (`choices`, `query_builder`, `attr`, ...).

## The built-in filters

| Filter | Operations | Value |
|---|---|---|
| `TextFilter` | contains, starts with, ends with, is, is not, is (not) filled | text |
| `NumericFilter` | is, is not, greater / less than (or equal), between, is (not) filled | integer or decimal by the path |
| `DateFilter` | is, is before, is after, between, is (not) filled | a day from the date picker |
| `BooleanFilter` | yes, no | none, the choice is the operation itself |
| `ChoiceFilter` | is one of, is none of, is, is not | one or several of `setChoices()` |
| `EntityFilter` | is one of, is none of, is (not) filled; has none / has some on a to-many path | related entities in a select, compared by their identifier |
| `ProductFilter` | is, is not | a product picked in the product picker window |

The operations of a filter are narrowed to those the adapter can evaluate on its path — `PathDescribingAdapterInterface::describePath()` tells the kind of value, `ExpressionOperatorApplicability` the rules — so a text search is never offered on a number. A filter whose path does not exist, leads to the wrong kind of value (an `EntityFilter` on a property) or keeps no operation at all is refused by `FilterNotApplicableException` when the datagrid is built.

A `DateFilter` over a date and time treats the picked day as a range in the time zone of the administration: "is 12.5." means from its midnight to the midnight after, "is before 12.5." means before its midnight, "is after 12.5." means from the midnight after. Over a date without time the day is compared as it is.

## How a composed filter is applied

The form starts with one group holding one empty rule, so the first rule is one click away; a submission without any rule shows the same starting point. The composed rules arrive in a GET form named `<gridId>_filter` — groups of rules, each group with its own `AND`/`OR`, and an `AND`/`OR` between the groups. `FilterCollection::createCondition()` turns them into the [condition tree](./narrowing.md#the-condition-tree) and the datagrid sends it to the adapter together with the domain control and its fixed conditions. The rules of an unknown filter or without a value narrow nothing.

The filter and the quick search are two ways of asking the same question, so only one of them applies: the filter whenever any rule was composed, the quick search otherwise. Submitting one form drops the parameters of the other.

An invalid filter (a malformed number, an operation the filter does not offer) narrows nothing and shows its errors instead of quietly listing more than the administrator asked for.

## The form on the page

The `Admin:Grid:Filters` component renders the form in the `datagrid_filter_panel` block of the CRUD list template, fed by the `filterForm` template parameter. The form lives in a panel sliding in from the side of the page (a Bootstrap offcanvas), opened by the "Advanced filter" button next to the quick search, which carries the number of applied rules. The panel is always closed on arrival, so the records stay in sight; what the listing is narrowed by is spelled out rule by rule on chips in the bar below the toolbar (the `Admin:Grid:ActiveFilters` component), the words between them saying how the rules are combined. A chip is a link taking its own rule back, marked by a cross; the group goes with its last rule and the whole filter with its last group. Beside the chips is the reset dropping everything at once. Besides the composed groups it renders a prototype of the operation select and the value input for every filter and every arity of its operations (`prototypes[<filter>][<arity>]`); the `datagrid-filter` Stimulus controller clones them when the administrator adds a rule or changes its filter or operation, so no request is needed until the filter is applied. The prototypes are never submitted.

Outside the CRUD controller: `Datagrid::getFilterForm()` gives the submitted form (null when no filter is declared or the datagrid is built outside a request), and `component('Admin:Grid:Filters', { filterForm: form, gridView: gridView })` renders it.

## Writing your own filter

A filter type answers four questions: which operations it offers, how its value is entered, whether it can work on the path it was declared on, and how a submitted rule becomes a condition. `AbstractFilter` answers all four with sensible defaults, so a new type overrides only what differs.

### 1. Name the operations and the value input

Extend `AbstractFilter` (or the closest built-in filter) and list the operations of the [vocabulary](narrowing.md#the-vocabulary) the filter offers, in the order the administrator sees them, and the form type entering the value:

```php
namespace App\Component\Datagrid\Filter;

use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\AbstractFilter;
use Shopsys\FrameworkBundle\Form\MoneyType;

final class MoneyFilter extends AbstractFilter
{
    protected function getDefaultOperators(): array
    {
        return [
            ExpressionOperatorEnum::EQUALS,
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL,
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL,
            ExpressionOperatorEnum::BETWEEN,
        ];
    }

    public function getValueFormType(string $operator): string
    {
        return MoneyType::class;
    }
}
```

That is already a complete filter. `resolveFor()` keeps only the operations the adapter supports on the path (a filter left with none is refused when the datagrid is built), the form renders the money input for a single value or a pair of bounds for `between`, and `buildCondition()` turns a rule into a `Comparison` on the path. `setOperators()` on the declaration narrows the list further, never widens it.

The name of the filter in the URL is derived from the path (`price`, `author_fullName` for `author.fullName`). Keep it stable — a renamed filter breaks bookmarked links — and call `setName()` only when two filters share a path.

### 2. Shape the value form

`getDefaultValueFormOptions()` returns the options of the value form type; the options passed to `setValueFormOptions()` on the declaration are merged on top, so the declaration always wins. The `FilterEnvironment` the filter was resolved for is at hand through `getEnvironment()` — it carries the adapter, the vocabulary, the applicability rules and the time zone of the administration, which is how `DateFilter` keeps the picked day in that time zone:

```php
protected function getDefaultValueFormOptions(string $operator): array
{
    return [
        'model_timezone' => $this->getEnvironment()->displayTimeZone->getName(),
    ];
}
```

`$this->pathDescription` (null when the adapter has no schema to describe the path from) tells the cardinality of the path, the kind of value it leads to and the entity class of an association. `NumericFilter` picks `IntegerType` over `NumberType` by it, `EntityFilter` takes the entity class of the select from it.

### 3. Refuse a path the filter cannot work with

Override `assertApplicable()` when the type needs a particular kind of path, so that a wrong declaration fails when the datagrid is built, not when the administrator submits it:

```php
protected function assertApplicable(FilterEnvironment $environment): void
{
    if ($this->pathDescription !== null && $this->pathDescription->valueType !== PathValueTypeEnum::MONEY) {
        throw new FilterNotApplicableException($this->getName(), sprintf('the path "%s" does not lead to money.', $this->path));
    }
}
```

`isOperatorApplicable()` is the per-operation counterpart — `BooleanFilter` overrides it because its `yes` and `no` are not operators of the vocabulary and the applicability rules would not know them.

### 4. Translate the rule when a plain comparison is not enough

`createComparison()` receives the operation and the normalized value (a single value, a list, or `[from, to]` for a range) and returns any condition of the [condition tree](narrowing.md#the-condition-tree):

- `EntityFilter` compares the identifier of the association instead of the association itself,
- `DateFilter` turns "is 12.5." into a range from midnight to midnight,
- `BooleanFilter` offers operations of its own that carry the value in themselves — it says through `getValueArity()` that `yes` and `no` take no value and translates them to `equals` in `buildCondition()`.

```php
protected function createComparison(string $operator, mixed $value): ?ConditionInterface
{
    if ($operator === ExpressionOperatorEnum::EQUALS) {
        // the input cannot express the stored precision, so "is" means "rounds to"
        return Condition::andX(
            Condition::greaterThanOrEqual($this->path, $value->subtract(Money::create('0.005'))),
            Condition::lessThan($this->path, $value->add(Money::create('0.005'))),
        );
    }

    return parent::createComparison($operator, $value);
}
```

Returning `null` means the rule narrows nothing. What the vocabulary cannot express at all (an aggregate, a computed value) is a [`DqlCondition`](narrowing.md#beyond-the-vocabulary) returned from here — the price is that the datagrid then works with the ORM adapter only, say so in the docblock of the filter.

Override `getOperatorLabel()` when an operation reads better in another word for the kind of value (`DateFilter` says "is before" instead of "is less than").

### 5. Declare it and test it

```php
$datagrid->filters()->add(MoneyFilter::new('price', t('Price')));
```

A filter is a plain object, so its unit test needs neither Doctrine nor a request: resolve it for a `FilterEnvironment` over an adapter describing the path, then assert the operations it offers and the condition `buildCondition()` builds from a `FilterRuleData`. `DateFilterTest` and `EntityFilterTest` in `packages/administration/tests/Unit/Component/Datagrid/Filter/` show the setup. A functional test of the list with the filter in the URL (see `FilterListTest` in the project) proves the whole path down to the rows.

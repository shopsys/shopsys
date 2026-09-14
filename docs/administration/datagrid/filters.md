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

The `Admin:Grid:Filters` component renders the form in the `datagrid_controls` block of the CRUD list template, fed by the `filterForm` template parameter. When the list offers the quick search as well, the two share one card as tabs — the tab of a composed filter is the open one — and the domain control moves into a card beside them as a compact select. Besides the composed groups it renders a prototype of the operation select and the value input for every filter and every arity of its operations (`prototypes[<filter>][<arity>]`); the `datagrid-filter` Stimulus controller clones them when the administrator adds a rule or changes its filter or operation, so no request is needed until the filter is applied. The prototypes are never submitted.

Outside the CRUD controller: `Datagrid::getFilterForm()` gives the submitted form (null when no filter is declared or the datagrid is built outside a request), and `component('Admin:Grid:Filters', { filterForm: form, gridView: gridView })` renders it.

## Writing your own filter

Extend `AbstractFilter` (or one of the built-in filters) and say which operations it offers and how its value is entered; the translation into a condition is inherited:

```php
final class ProductFilter extends EntityFilter
{
    protected function getDefaultOperators(): array
    {
        return [ExpressionOperatorEnum::EQUALS, ExpressionOperatorEnum::NOT_EQUALS];
    }

    public function getValueFormType(string $operator): string
    {
        return ProductType::class; // the product picker window instead of a select
    }
}
```

Override `createComparison()` when the value compares differently from a plain comparison of the path (see `DateFilter` and `EntityFilter`), `assertApplicable()` to refuse a path the filter cannot work with, and `getOperatorLabel()` when an operation reads better in another word for the kind of value. A filter may even offer operations of its own that carry the value in themselves — `BooleanFilter` offers "yes" and "no" with `getValueArity()` saying none, and translates them to `equals` in `buildCondition()`. What the vocabulary cannot express at all is a `DqlCondition` returned from `buildCondition()`, with the datagrid bound to the ORM adapter as the price.

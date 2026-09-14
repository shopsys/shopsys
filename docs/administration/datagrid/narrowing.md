# Narrowing the records

A datagrid lists the records its adapter loads. Everything that narrows them — the domain control, a quick
search, a filter — does so through a **condition**: a small tree of plain data every adapter understands and
compiles into its own medium. The code narrowing a datagrid never sees a `QueryBuilder`, so the same code
works over a Doctrine query and over records already in memory.

## Asking the adapter to narrow

```php
$dataSource = $adapter->getDatasource(new DatasourceRequest(
    'id',
    $fields,
    Condition::contains('customerUser.email', 'novak'),
));
```

`AdapterInterface::getDatasource()` takes everything one listing asks for as a `DatasourceRequest` — the
identification name, the fields and an optional condition. The adapter keeps no state between requests, which
is what makes the contract safe and simple:

- what belongs to the medium itself (a fixed scope, a default join) is given to the adapter once when it is
  created — `configureQuery()` of the ORM adapter; what one listing asks for arrives in its request, so
  a datagrid renders its view any number of times and two datagrids can share one adapter,
- the condition is compiled by the builder of the adapter while the data source is built — never sooner —
  so an expression can never come from a foreign medium, and preparing the medium for it (joining an
  association, registering a parameter) happens only for conditions that are actually applied,
- a request without a condition lists every record.

`Datagrid::createView()` sends such a request itself, combining by `Condition::andX()` everything that
narrows the datagrid: the condition of the domain control, the quick search or the filter, and the fixed
conditions added by `Datagrid::addCondition()`.

```php
// a fixed scope the administrator neither sees nor switches off
$datagrid->addCondition(Condition::equals('deleted', false));
```

## Quick search

Declaring a field `searchable` is all it takes:

```php
$datagrid
    ->add('catnum', ['label' => t('Catalog number'), 'searchable' => true])
    ->add('name', ['label' => t('Name'), 'searchable' => true]);
```

The datagrid renders a text input above the records (the `Admin:Grid:QuickSearch` component, added to the
`datagrid_controls` block of the CRUD list template) and narrows the records by `Condition::orX()` of
`contains` over every searchable field. The searched text travels in a GET form named `<gridId>_search`,
so the pager keeps it and the URL can be shared; the order and the limit of the grid travel with the search
and the page starts over.

A searchable field is validated when the datagrid is built: it has to have a path of its own (a virtual
field without a `property` has none) and the path has to lead to text (asked through
`PathDescribingAdapterInterface::describePath()`), otherwise `FieldNotSearchableException` says which field
and why. A field hidden by `visible: false` can still be searchable.

Outside the CRUD controller the pieces are `Datagrid::getQuickSearch()` (null when no field is searchable or
the datagrid is built outside a request) and the component: `component('Admin:Grid:QuickSearch', { quickSearch: datagrid.quickSearch, gridView: gridView })`.

## Filters

Rules the administrator composes — grouped, combined by `AND` or `OR` — are declared as filters on the datagrid
and rendered as a form above the records; see [Filters](./filters.md). A composed filter outranks the quick search.

## The condition tree

A condition is built by the named constructors of `Condition`, one per operation of the vocabulary:

```php
Condition::andX(
    Condition::in('domainId', [1, 2]),
    Condition::orX(
        Condition::contains('name', 'hrnek'),
        Condition::contains('catnum', 'hrnek'),
    ),
    Condition::not(Condition::isEmpty('images')),
)
```

The tree has three kinds of nodes — `Comparison` (one operation on one path), `Composite` (`AND` / `OR`) and
`Negation`. When the operation is only known at runtime — an administrator chose it in a filter and it
arrived as a string — build the comparison directly as `new Comparison($path, $operator, $value)`. Being
data, a condition can be composed, inspected, serialized and asserted on without any medium at hand.

## The vocabulary

The operations are the cases of `ExpressionOperatorEnum`, and every adapter expresses them by the methods
of its `ExpressionBuilderInterface` of the same names:

| Operation | Meaning |
|---|---|
| `equals`, `notEquals` | the value is (not) the given one |
| `contains`, `startsWith`, `endsWith` | the text contains / starts with / ends with the given one, ignoring case and diacritics (`NORMALIZED()` in the database, the same rule in memory); wildcards are escaped, the value is taken literally |
| `greaterThan`, `greaterThanOrEqual`, `lessThan`, `lessThanOrEqual`, `between` | ordering of numbers, dates and money |
| `in`, `notIn` | the value is (not) one of the given ones; an empty list matches nothing, resp. everything |
| `isNull`, `isNotNull` | a single value is (not) missing |
| `isEmpty`, `isNotEmpty` | a record has no / some related row on a to-many path |
| `not`, `andX`, `orX` | composition |

Values are typed — `string|int|float|bool|DateTimeInterface|Money` — and a builder binds them with the type of
the compared field, so a date or money compares correctly. The shape of the value of a comparison is given by
the arity of its operator (`ExpressionValueArityEnum`: none, a single value, a list, a pair of bounds) and is
validated when the condition is compiled.

### Paths

A path is the same dot notation the fields of the datagrid use: `email`, `customerUser.email`,
`customerUser.billingAddress.city`, `items.name`. In the ORM medium the associations on the way are joined
outwardly (`LEFT JOIN`), so a record without the association never disappears from the listing only because a
condition mentions it. A path leading through a to-many association (`items.name`) is matched by a correlated
`EXISTS` subquery, so the listed record is never multiplied by its related rows. A field of the entity shadows a
translated field of the same name; the translation is reachable explicitly as `translations.name`.

### Two things worth knowing about collections

`Condition::isNull('items.name')` is refused — the subquery joins the related rows inwardly, so it could never
match. The question an administrator means, "products without any image", is `Condition::isEmpty('images')`.

Negating a whole condition is not the negating operation of that condition once the path leads through a
collection:

```php
Condition::not(Condition::equals('items.name', 'X'));   // no related row is named X
Condition::notEquals('items.name', 'X');                // some related row is not named X
```

A record with items named X and Y matches the second one and not the first. Pick knowingly.

## Asking before building

A declaration — a searchable field, a filter offering operators — has to know what it can offer before any
query exists. Two answers are available without building anything:

- `AdapterInterface::getExpressionCapabilities()->supportsOperator($operator)` tells whether the medium
  expresses the operation at all;
- an adapter implementing the optional `PathDescribingAdapterInterface` describes a path by
  `describePath($path)` — its cardinality (`TO_ONE` / `TO_MANY`) and the kind of value it leads to
  (`PathValueTypeEnum`: `STRING`, `INTEGER`, `DECIMAL`, `BOOLEAN`, `DATE`, `DATETIME`, `MONEY`,
  `ASSOCIATION`, `UNKNOWN`) — without touching the query. `OrmAdapter` implements it; an adapter over records
  in memory has no schema to describe and does not.

`ExpressionOperatorApplicability::assertApplicable($operator, $pathDescription)` holds the rules saying which
operation makes sense on which path — a text search on a text value only, a null test on a single value only,
an emptiness test on a collection only. The ORM builder applies them itself before building, a declaration
applies them before offering an operation.

## How a condition becomes an expression

`ExpressionCompilerInterface::compile($expressionBuilder, $condition)` walks the tree and calls the builder —
`Comparison` becomes the method of its operator, `Composite` becomes `andX()` / `orX()`, `Negation` becomes
`not()`. Every adapter calls the compiler with its own builder while building the data source, so it is the
one place the vocabulary meets the methods and the one place the shape of every value is validated.

Both built-in media are proven to agree: the functional test `ExpressionMediaAgreementTest` runs the same
conditions against the database and against the same records in memory and requires the same result.

## Beyond the vocabulary

The vocabulary is closed on purpose — it is what every medium has to understand. An aggregate ("orders with
more than three items"), a computed value or a `HAVING` clause is said to the Doctrine query directly by a
`DqlCondition`, a `MediumSpecificConditionInterface` only the ORM medium compiles:

```php
new DqlCondition(
    static fn (QueryBuilder $queryBuilder, ProxyQuery $proxyQuery): string
        => sprintf('SIZE(o.items) > :%s', $proxyQuery->addParameter(3)),
)
```

The closure returns a DQL predicate and runs when the data source is built, on the very query it is built
from. It composes with the rest of the tree — it may sit in an `OR` group next to ordinary conditions — but
the datagrid then works with the ORM adapter only; given the builder of another medium the condition throws
`ConditionNotSupportedException`. Use `ProxyQuery::addParameter()` for every value and
`ProxyQuery::resolvePath()` for every path, so the query stays parameterized and the joins stay shared with
the rest of the datagrid.

`AbstractCrudController::configureQuery()` is something else: it shapes the query statically when the adapter
is created — a fixed scope, a default join — and runs without any request. A condition driven by the
administrator never belongs there.

## Extending the medium in a project

The datagrid classes are `final`; the medium is extended through its services, never by inheritance:

- **a new operation** (`jsonContains`, a project collation for `contains`) — implement your own DQL builder
  behind `DqlExpressionBuilderFactoryInterface` (implementing `DqlExpressionBuilderInterface`, typically
  wrapping the default builder and adding to it) and register your factory under that interface;
- **a new operator code or a new kind of node** — subclass `ExpressionOperatorEnum` or implement
  `ConditionInterface`, and decorate `ExpressionCompilerInterface` to compile it, handing every other
  condition to the decorated compiler; a node bound to one medium implements `MediumSpecificConditionInterface`
  and compiles itself instead;
- **a new medium** — implement `AdapterInterface` with an `ExpressionBuilderInterface` of your own and call the
  compiler on the condition of every request; wrap the native expression of the medium in an object if it is
  not one (a search engine clause is an array).

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
  created — the closure passed to `OrmAdapterFactory::create()`, `configureQuery()` of a CRUD controller; what
  one listing asks for arrives in its request, so a datagrid renders its view any number of times and two
  datagrids can share one adapter,
- the condition is compiled by the builder of the adapter while the data source is built — never sooner —
  so an expression can never come from a foreign medium, and preparing the medium for it (joining an
  association, registering a parameter) happens only for conditions that are actually applied,
- a request without a condition lists every record, and an adapter applies the condition of every request — it
  carries the limit of the domain control as well, so an adapter ignoring it lists the records of every domain.

`Datagrid::createView()` sends such a request itself. Its condition is the one of the state of the datagrid —
everything that narrows the datagrid in the current request (the domain control, and the quick search or the filter)
combined by `Condition::andX()`, see [Configuration, state and components](#configuration-state-and-components).

A fixed scope the administrator neither sees nor switches off ("only records that are not deleted") is not a
condition of the request but a part of the query itself, so it belongs to `configureQuery()` of the CRUD
controller:

```php
protected function configureQuery(QueryBuilder $queryBuilder): void
{
    $queryBuilder
        ->andWhere('o.deleted = :deleted')
        ->setParameter('deleted', false);
}
```

## Quick search

Declaring a field `searchable` is all it takes:

```php
$datagrid
    ->add('catnum', ['label' => t('Catalog number'), 'searchable' => true])
    ->add('name', ['label' => t('Name'), 'searchable' => true]);
```

The datagrid renders a text input above the records (the `Admin:Grid:QuickSearch` component, rendered by
the `Admin:Grid:Controls` component in the `datagrid_controls` block of the CRUD list template; next to a filter it
becomes a tab of the same card) and narrows the records by `Condition::orX()` of
`contains` over every searchable field. The searched text travels in a GET form named `<gridId>_search`,
so the pager keeps it and the URL can be shared; the order and the limit of the grid travel with the search
and the page starts over.

A searchable field is validated when the quick search is built from the fields: it has to have a path of
its own (a virtual field has none) and the path has to lead to text (asked through
`PathDescribingAdapterInterface::describePath()`), otherwise `FieldNotSearchableException` says which field
and why. An adapter that cannot describe its paths (records in memory) cannot be asked, so every property of
a searchable field is searched and has to hold text — a number or an enum fails only when something is searched,
by `OperatorNotApplicableToValueException`. A field hidden by `visible: false` can still be searchable.

The quick search is the `QuickSearch` part of the state of the datagrid — `$datagridState->get(QuickSearch::class)`,
null when no field is searchable or the state is created outside a request. It carries the name of its form, the
searched term and the searched fields; the component builds the form from it.

## Filters

Rules the administrator composes — grouped, combined by `AND` or `OR` — are declared as filters on the datagrid
and rendered as a form above the records; see [Filters](./filters.md). A composed filter outranks the quick search:
once the administrator composed a rule (`FilterPanel::isActive()`), the filter narrows the records and the
searched term is not applied.

## Configuration, state and components

A datagrid is listed in three steps, each of them knowing as little about the others as possible:

1. **Configuration.** The datagrid is configured — fields, filters, the domain control — and
   `Datagrid::getConfig()` freezes it into a read-only `DatagridConfig`: the ID of the datagrid, its adapter,
   the fields the administrator is allowed to see and the declared filters the administrator is allowed to use
   (those not declared under the name of a field hidden from the administrator or over a path overlapping one of its
   properties — the property itself, a path under it or the association it lies under). A field or a filter changed
   afterwards would never reach the state — a field restricted by a role would keep
   being searched in — so `add()`, `update()`, `remove()` and `filters()` refuse to change the datagrid then.
2. **State.** `DatagridStateFactory::create($config)` dispatches a `DatagridStateEvent` and every feature narrowing
   the datagrid adds its part of the state by a subscriber of its own — `DomainControlStateSubscriber` adds
   the `DomainControlScope`, `QuickSearchStateSubscriber` the `QuickSearch` and `FilterStateSubscriber`
   the `FilterPanel`. The quick search and the filter validate their declarations even outside a request (a test or
   a command fails on a field that cannot be searched as well), the domain control needs a request of a logged-in
   administrator, whose domains and selection it reads. Each of them reads its own part of the main request; a GET
   form is submitted from the query by hand, once. The factory then combines the conditions of the parts into the one of the
   `DatagridState`: every scope (`DatagridScopeInterface`, the domain control) by `AND` with the one search
   (`DatagridSearchInterface`) that applies (`DatagridState::getAppliedSearch()`) — the filter whenever any rule was
   composed, the quick search otherwise.
3. **View and components.** `Datagrid::createView($datagridState)` lists the records by the condition of the state,
   and the `Admin:Grid:Controls` component renders the part of every feature by the component of that feature —
   the tab of the applied search is the open one.
   A state of something the administrator asked for that cannot be applied — an invalid filter — is
   `isInvalid()`, the page answers 422 then.

The CRUD list does all of it in `listAction()`. A page of your own lists a datagrid the same way:

```php
$datagrid = $this->datagridFactory->create($adapter, [
    'name' => 'order',
    'roleConstant' => 'ROLE_MY',
    // optional - the domain control of a CRUD datagrid follows the configuration of the CRUD controller
    'domainControl' => new DomainControlConfig(DomainControlType::SWITCHER),
]);
$datagrid->add('number', ['label' => t('Number'), 'searchable' => true]);

$datagridState = $this->datagridStateFactory->create($datagrid->getConfig());
$response = $this->render('my_template.html.twig', [
    'grid' => $datagrid->createView($datagridState),
    'datagridState' => $datagridState,
]);

if ($datagridState->isInvalid()) {
    $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
}

return $response;
```

```twig
{{ component('Admin:Grid:Controls', { state: datagridState, gridView: grid }) }}
{{ grid.render }}
```

The state is required by `createView()` and the factory refuses to create the state of a datagrid with a domain
control when no subscriber limited it to the domains, so the domain control is never left out by mistake.

### Changing what narrows the datagrid

- **which search outranks which** — extend `DatagridStateFactory` and override `getSearchPrecedence()` (or
  `createCondition()` to combine the parts differently), then alias the service to your class;
- **how a feature reads the request** — extend its subscriber (`QuickSearchStateSubscriber`, …) and alias the
  service to your class; a part of the same class can be added only once, so a second subscriber adding it fails
  instead of silently competing with the first one;
- **a new way of narrowing** (filters saved by the administrator, for one) — add a subscriber of
  `DatagridStateEvent` adding a part of its own implementing `DatagridSearchInterface` (or `DatagridScopeInterface`
  for a scope that always applies) and render it by a component of your own, in the `datagrid_controls` block
  of the list template next to the built-in controls (`{{ parent() }}`). A search not ranked by
  `getSearchPrecedence()` applies when no ranked one is active; among such searches the one added first — by
  the listener of the higher priority — applies. A field displayed on behalf of the feature is added by
  `DatagridStateEvent::addField()` and obeys the `role` and `permission` options as any other field.

The smaller services the subscribers use are replaceable as well — `SearchablePathResolver` finds the paths
of the `searchable` fields and refuses a field that cannot be searched, `FilterSetResolver` merges the filters
picked for the `filterable` fields (by `FilterGuesser`) with the declared ones, sorts them by label and adapts copies
of them to the adapter, so a declaration is never changed, and `QuickSearchFormFactory` and `FilterFormFactory` build
the forms.

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

The operations are the cases of `ExpressionOperatorEnum`, which also knows everything about each of them that
does not depend on the medium — the shape of its value (`getValueArity()`), the paths it makes sense on
(`assertApplicable()`) and how it reads in the filter form (`getLabel()`). Every medium expresses every one of
them:

| Operation | Meaning |
|---|---|
| `equals`, `notEquals` | the value is (not) the given one |
| `contains`, `startsWith`, `endsWith` | the text contains / starts with / ends with the given one, ignoring case and diacritics (`NORMALIZED()` in the database, the same rule in memory); wildcards are escaped, the value is taken literally |
| `greaterThan`, `greaterThanOrEqual`, `lessThan`, `lessThanOrEqual`, `between` | ordering of numbers, dates and money |
| `in`, `notIn` | the value is (not) one of the given ones; an empty list matches nothing, resp. everything |
| `isNull`, `isNotNull` | a single value is (not) missing |
| `isEmpty`, `isNotEmpty` | a record has no / some value on a to-many path — a related row without the value (an order item without a product for `items.product`) counts as none |
| `not`, `andX`, `orX` | composition |

A record whose value is missing never matches a comparison, but always matches its negation — `notEquals`,
`notIn` and `not()`: an order without a customer is "not Novák". SQL alone would leave such a record out even
from a negation, so the ORM medium tests the missing value explicitly; the medium over records in memory decides
the same way.

Values are typed — `string|int|float|bool|DateTimeInterface|Money` — and a builder binds them with the type of
the compared field, so a date or money compares correctly. A date of either class compares with a column of either
class, and a date and time given in any time zone means the same moment — the ORM medium converts it to the default
time zone the stored time is read in; a date without time is a day of the calendar and stays as it is. The shape of
the value of a comparison is given by the arity of its operator (`ExpressionValueArityEnum`: none, a single value,
a list, a pair of bounds) and is validated when the condition is compiled. Every value of a list is bound on its own,
so a list of booleans or dates compares correctly as well; a missing value is tested by `isNull`, never put in a list
or used as a bound.

### Paths

A path is the same dot notation the fields of the datagrid use: `email`, `customerUser.email`,
`customerUser.billingAddress.city`, `items.name`. In the ORM medium the associations on the way are joined
outwardly (`LEFT JOIN`), so a record without the association never disappears from the listing only because a
condition mentions it; a field may select the associated entity itself (`$datagrid->add('currency')`). A path leading through a to-many association (`items.name`) is matched by a correlated
`EXISTS` subquery, so the listed record is never multiplied by its related rows. Inside the subquery a to-one
association is joined outwardly as well — an order item without a product has a missing value in
`items.product.catnum`, which a negation matches the way it matches a listed record without the association — while
an empty collection has no related row at all. A field of the entity shadows a translated field of the same name;
the translation is reachable explicitly as `translations.name`. A field of a Doctrine embeddable is addressed
through the embeddable, `author.address.country`.

### Two things worth knowing about collections

`Condition::isNull('items.name')` is refused — a collection has no single value to be missing. The question
an administrator means, "products without any image", is `Condition::isEmpty('images')`.

Negating a whole condition is not the negating operation of that condition once the path leads through a
collection:

```php
Condition::not(Condition::equals('items.name', 'X'));   // no related row is named X
Condition::notEquals('items.name', 'X');                // some related row is not named X
```

A record with items named X and Y matches the second one and not the first. Pick knowingly — a filter offering
"is not" or "is none of" on a collection negates the whole condition, which is what the administrator means.

## Asking before building

A declaration — a searchable field, a filter offering operators — has to know what it can offer before any
query exists. An adapter implementing the optional `PathDescribingAdapterInterface` describes a path by
`describePath($path)` — its cardinality (`TO_ONE` / `TO_MANY`), the kind of value it leads to
(`PathValueTypeEnum`: `STRING`, `INTEGER`, `DECIMAL`, `BOOLEAN`, `DATE`, `DATETIME`, `MONEY`, `ASSOCIATION`,
`UNKNOWN`) and, for an integer, the range its column holds — without touching the query. `OrmAdapter` implements it and reads an own Doctrine type of a project
extending a text type as text; a GUID, a JSON document or any other type is `UNKNOWN`. An adapter over records
in memory has no schema to describe and does not implement it.

`ExpressionOperatorEnum::assertApplicable($operator, $pathDescription)` holds the rules saying which
operation makes sense on which path — a text search on a text value only (never on an `UNKNOWN` one, which
a text function of the database may refuse), a null test on a single value only, an emptiness test on
a collection only. The ORM builder applies them itself before building, a declaration applies them before
offering an operation.

## How a condition becomes an expression

`ExpressionBuilderInterface` has a single method, `compile($condition)`. Every builder extends
`AbstractExpressionBuilder`, which walks the tree — `Comparison` becomes the protected `compare()` of its
operator, `Composite` becomes `andX()` / `orX()`, `Negation` becomes `not()` and
a `MediumSpecificConditionInterface` compiles itself. Every adapter calls it on its own builder while building
the data source, so the walk is shared by every medium and the shape of every value is validated in one place,
before the medium is asked. A medium only expresses each operation in `compare()` and composes the expressions.

Both built-in media are proven to agree: the functional test `ExpressionMediaAgreementTest` runs the same
conditions against the database and against the same records in memory and requires the same result.

## Beyond the vocabulary

The vocabulary is closed on purpose — it is what every medium has to understand. An aggregate of the related
rows ("orders with more than three items") or a computed value is said to the Doctrine query directly by a
`DqlCondition`, a `MediumSpecificConditionInterface` only the ORM medium compiles. It is a predicate of the `WHERE`
clause — the listing has no grouping a `HAVING` clause could narrow:

```php
new DqlCondition(
    static fn (ProxyQuery $proxyQuery): string
        => sprintf('SIZE(%s.items) > :%s', ProxyQuery::DEFAULT_ALIAS, $proxyQuery->addParameter(3)),
)
```

The closure returns a DQL predicate and runs when the data source is built, on the very query it is built
from. It composes with the rest of the tree — it may sit in an `OR` group next to ordinary conditions — but
the datagrid then works with the ORM adapter only; given the builder of another medium the condition throws
`ConditionNotSupportedException`. Use `ProxyQuery::addParameter()` for every value and
`ProxyQuery::resolvePath()` for every path, so the query stays parameterized and the joins stay shared with
the rest of the datagrid. A path leading through a collection is never joined, it would multiply the listed
record — `ProxyQuery::exists($path, $buildPredicate)` matches a related row by a correlated subquery instead.
The root entity of the query is aliased `ProxyQuery::DEFAULT_ALIAS` and the query builder itself is at hand
through `ProxyQuery::getQueryBuilder()`.

`AbstractCrudController::configureQuery()` is something else: it shapes the query statically when the adapter
is created — a fixed scope, a default join, a computed column — before the datagrid reads what the administrator
searched for or filtered by. A condition driven by the administrator never belongs there; a `DqlCondition` is what
a filter returns when its rule needs DQL.

## Extending the vocabulary in a project

- **a new operation** (`jsonContains`, a project collation for `contains`) — subclass `ExpressionOperatorEnum`:
  add the public constant and override `getValueArity()`, `getReasonOfInapplicability()` and `getLabels()` for
  it, falling back to the parent for every other operation, and register the subclass in place of
  `ExpressionOperatorEnum`. Then teach every medium you use the operation: extend its builder
  (`DqlExpressionBuilder`, `PhpExpressionBuilder`), express the new operator in `compare()` and hand every other
  one to `parent::compare()`. A medium you do not teach refuses the operation by `OperatorNotSupportedException`
  when it compiles a condition using it. Register the builders in place of the default ones: the ORM adapter
  creates a builder for every query, so alias `DqlExpressionBuilderFactoryInterface` to a factory of your own
  creating your builder; the array adapter uses one stateless builder, so alias `ExpressionBuilderInterface` to
  your builder (see the registration below);
- **a new kind of node** — a node bound to one medium implements `MediumSpecificConditionInterface` and
  compiles itself, the way `DqlCondition` does; a node every medium of yours understands is compiled by
  overriding `compile()` of your builder, handing every other condition to `parent::compile()`;
- **a different reading of a path** (the value type of an own Doctrine type of the project not extending a type
  of Doctrine) — alias `PathWalkerInterface` to a walker of your own, which wraps the final `PathWalker` and
  adjusts the `PathDescription` of the walked path;
- **a new medium** — implement `AdapterInterface` with a builder of your own extending
  `AbstractExpressionBuilder` and call its `compile()` on the condition of every request; wrap the native
  expression of the medium in an object if it is not one (a search engine clause is an array).

The `services.yaml` of the project registers a new operation like this — the enum and the factory are services
of the project by their names already, the builder of the array adapter is registered explicitly:

```yaml
services:
    Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum:
        alias: App\Component\Datagrid\Expression\ExpressionOperatorEnum

    Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactoryInterface:
        alias: App\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactory

    App\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder: ~

    Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface:
        alias: App\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder
```

# Narrowing the records

The domain control, the quick search and the filters narrow the records a datagrid lists.
The second half of this page is for authors of filters and adapters.

## Quick start

```php
protected function configureDatagrid(Datagrid $datagrid): void
{
    $datagrid
        ->add('number', ['label' => t('Order Nr.'), 'searchable' => true])   // the quick search looks here
        ->add('createdAt', ['label' => t('Created'), 'filterable' => true]); // the filter offers a DateFilter

    $datagrid->filters()->add(TextFilter::new('customerUser.email', t('Customer e-mail')));
}

protected function configureQuery(QueryBuilder $queryBuilder): void
{
    $queryBuilder->andWhere('o.deleted = :deleted')->setParameter('deleted', false); // a fixed condition
}
```

## Where a condition belongs

| The condition depends on | Declare it in |
|---|---|
| the selected domain | the [domain control](../crud-controller/reference/crud-controller.md#list-domain-control) |
| a text the administrator types | a `searchable` field, see [Quick search](#quick-search) |
| rules the administrator composes | [filters](./filters.md) |
| a rule the operations cannot express (an aggregate, a computed value) | a filter returning a [`DqlCondition`](./filters.md#a-condition-in-dql) |

Any other condition of the list belongs to [`configureQuery()`](../crud-controller/reference/crud-controller.md#configurequeryquerybuilder-querybuilder-void) of the CRUD controller, or outside of it to the closure passed to `OrmAdapterFactory::create()`.

## Quick search

Declare a field `searchable`. The datagrid renders a text input above the records and lists the records in which any searchable field contains the typed text, ignoring case and diacritics. The text is taken literally and travels in the URL, so a link to the searched list can be shared.

```php
use Shopsys\AdministrationBundle\Component\Datagrid\Search\Searchable;

$datagrid
    ->add('email', ['label' => t('E-mail'), 'searchable' => true])
    ->add('customer', [
        'label' => t('Customer'),
        'property' => ['lastName', 'firstName', 'customerUser.id'],
        'searchable' => new Searchable(paths: ['lastName', 'firstName'], label: t('Customer name')),
    ])
    ->add('uuid', ['label' => t('UUID'), 'visible' => false, 'searchable' => true]);
```

- `'searchable' => true` searches in every property of the field. Each of them has to lead to text or to a UUID, otherwise `InvalidDatagridDeclarationException` is thrown when the state of the datagrid is created.
- `new Searchable(paths: [...], label: ...)` searches in the listed properties of the field only and names the field in the placeholder "Search in: …" of the input.
- A UUID (a Doctrine `guid` column) is matched for equality, and only when the typed text is a valid UUID.
- A virtual field, a number, a date or money cannot be searched — offer a [filter](./filters.md) of it instead.

`ProductReviewController::configureDatagrid()` shows all of it on a shipped list.

## A datagrid outside the CRUD controller

The CRUD list does all of this in `listAction()`. A page of your own lists a datagrid the same way:

```php
$datagrid = $this->datagridFactory->create($adapter, [
    'name' => 'order',
    'roleConstant' => 'ROLE_MY',
    // optional - a datagrid outside the CRUD controller has no domain control otherwise
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

- The state holds what narrows the datagrid in the current request — the selected domain, the searched text and the composed filter. `isInvalid()` is true when the composed filter cannot be applied.
- `getConfig()` freezes the configuration, so `add()`, `update()`, `remove()` and `filters()` throw afterwards.
- To render the filter alone, pass `$datagridState->get(FilterPanel::class)` to the template and render `component('Admin:Grid:Filters', { filter: filterPanel, gridView: grid })`.
- Outside a request (a command, a test), the state validates the searchable fields and the filters as well, and nothing is searched or filtered. A datagrid with a domain control needs a logged-in administrator.

## For authors of filters and adapters

### The condition tree

Everything that narrows a datagrid becomes a condition — plain data every adapter compiles in its own way (DQL for `OrmAdapter`, a callback for `ArrayAdapter`):

```php
// orders of the domains 1 and 2 whose number or e-mail contains "novak" and which have some item
Condition::andX(
    Condition::in('domainId', [1, 2]),
    Condition::orX(
        Condition::contains('number', 'novak'),
        Condition::contains('email', 'novak'),
    ),
    Condition::not(Condition::isEmpty('items')),
)
```

`Condition` has a named constructor for every operation of the vocabulary, and `andX()`, `orX()` and `not()` compose the conditions.
The tree has three kinds of nodes: `Comparison` (one operation on one path), `Composite` (`AND` / `OR`) and `Negation`. A [`DqlCondition`](./filters.md#a-condition-in-dql) is a fourth one, understood by the ORM adapter only.
An operation known only at runtime (one chosen in a filter) is built as `new Comparison($path, $operator, $value)`.

A filter returns such a tree from `buildCondition()`. An adapter receives the combined condition as `DatasourceRequest::$condition`; null lists every record. The condition carries the limit of the domain control as well, so an adapter ignoring it lists the records of every domain.

### The vocabulary

The operations are the constants of `ExpressionOperatorEnum`. The enum also knows the shape of the value of each operation (`getValueArity()`), the paths it makes sense on (`findReasonOfInapplicability()`) and its label in the filter form (`getLabel()`).

| Operation | Meaning |
|---|---|
| `equals`, `notEquals` | the value is (not) the given one |
| `contains`, `startsWith`, `endsWith` | the text contains / starts with / ends with the given one, ignoring case and diacritics; the given text is taken literally |
| `greaterThan`, `greaterThanOrEqual`, `lessThan`, `lessThanOrEqual`, `between` | ordering of numbers, dates and money; `between` includes both bounds |
| `in`, `notIn` | the value is (not) one of the given ones; an empty list matches nothing, resp. everything |
| `isNull`, `isNotNull` | a single value is (not) missing |
| `isEmpty`, `isNotEmpty` | a record has no / some value on a to-many path |

A record whose value is missing matches no comparison except `isNull`, so it matches the negation of any other one — `notEquals`, `notIn` and `not()`. An order without a customer is "not Novák".

Values are typed — `string|int|float|bool|DateTimeInterface|Money` — and their shape follows the operation (`ExpressionValueArityEnum`: none, a single value, a list, a pair of bounds). The ORM adapter binds each value with the type of the compared column, so dates and money compare correctly.

### Paths and collections

A path is the dot notation the fields use: `email`, `customerUser.email`, `customerUser.billingAddress.city`, `items.name`.

- The ORM adapter joins the associations on the way by `LEFT JOIN`, so a record without the association stays in the list.
- A path through a to-many association (`items.name`) is matched by an `EXISTS` subquery, so the listed record is not multiplied by its related rows. An order item without a product has a missing value in `items.product.catnum`.
- A translated field is reached by its name (`name`). When the entity has a field of the same name, the translated one is `translations.name`.
- A field of a Doctrine embeddable is addressed through the embeddable: `author.address.country`.

A collection has no single value, so `Condition::isNull('items.name')` is refused by the ORM adapter. "Orders without any item" is `Condition::isEmpty('items')`.

Over a collection, negating a whole condition is not the same as the negating operation:

```php
Condition::not(Condition::equals('items.name', 'X'));   // no related row is named X
Condition::notEquals('items.name', 'X');                // some related row is not named X
```

An order with items named X and Y matches the second condition only. What a filter offers over a collection is described in [The built-in filters](./filters.md#the-built-in-filters).

### Describing a path

An adapter implementing `PathDescribingAdapterInterface` describes a path by `describePath($path)` without building a query: its cardinality (`PathCardinalityEnum`), the kind of value it leads to (`PathValueTypeEnum`, `GUID` and `MONEY` included), the entity an association leads to and the range of an integer column. The searchable fields and the filters use it to refuse a wrong declaration when the state of the datagrid is created. `OrmAdapter` implements it and reads an own Doctrine type extending a text type as text; `ArrayAdapter` has no schema to describe.

`ExpressionOperatorEnum::findReasonOfInapplicability($operator, $pathDescription)` holds the rules and returns `null` for an operation that applies: a text operation on text only, `isNull` / `isNotNull` on a single value only, `isEmpty` / `isNotEmpty` on a collection only.

### Beyond the vocabulary

A condition the operations cannot express is written in DQL by a filter of your own, see [A condition in DQL](./filters.md#a-condition-in-dql).

### Differences between the adapters

`ArrayAdapter` evaluates the same conditions over records in memory (`ExpressionMediaAgreementTest` in the project compares the two adapters on the same orders). It differs from the ORM adapter in these cases:

- The rules of `findReasonOfInapplicability()` are not checked in advance — `isNull` over a collection passes, while the ORM adapter refuses it.
- A value of a wrong kind fails only when the first record is compared, by `UnsupportedConditionException` — a text operation on a number, a date compared with a number, a boolean or a text that is no number compared with a number or money.
- A value is compared by its PHP type, not by the type of its column. A number stored as text is compared as a number (the database compares text). A date column compared with a date and time is compared as a moment (the database drops the time).
- Text is ordered byte by byte (`strcmp()`), the database orders it by its collation.
- A filter's "is not" and "is none of" on a collection mean "some related row is not X" (see [Paths and collections](#paths-and-collections)). The adapter does not describe the path as a collection, so the filter does not negate the whole condition.

## Extending

The parts of the state, the Twig components rendering them (`Admin:Grid:*`) and the services of the ORM adapter are final. A project adds a part of its own, or registers its own implementation of an interface in place of a service. These classes are open to be extended:

- the filters, `FilterGuesser`, `FilterSetResolver` and `FilterFormFactory`;
- `SearchablePathResolver` and `QuickSearchFormFactory`;
- the expression builders and `ExpressionOperatorEnum`;
- `DatagridStateFactory` and the subscribers adding the parts.

### A search or a scope of your own

The domain control, the quick search and the filter are parts of the state of the datagrid — `DomainControlScope`, `QuickSearch` and `FilterPanel` — each added by its subscriber of `DatagridStateEvent`. A part of your own is added by a subscriber of your own:

- a search the administrator asks for implements `DatagridSearchInterface`;
- a scope that always applies implements `DatagridScopeInterface`. Its condition is combined with the others by `AND`.

A search by an EAN scanned by a barcode reader:

```php
namespace App\Component\Datagrid;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridSearchInterface;

final readonly class EanSearch implements DatagridSearchInterface
{
    public function __construct(
        public string $formName,
        public ?string $ean,
    ) {
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->ean !== null;
    }

    #[Override]
    public function createCondition(): ?ConditionInterface
    {
        return $this->ean === null ? null : Condition::equals('ean', $this->ean);
    }
}
```

```php
namespace App\Component\Datagrid;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EanSearchStateSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [DatagridStateEvent::class => 'onDatagridState'];
    }

    public function onDatagridState(DatagridStateEvent $event): void
    {
        // the datagrid of a CRUD controller is named after its entity
        if ($event->config->gridId !== 'Product') {
            return;
        }

        $formName = $event->config->createFormName('_ean');
        $ean = $event->getQuery($formName)['ean'] ?? null;

        $event->addPart(new EanSearch($formName, is_string($ean) && $ean !== '' ? $ean : null));
    }
}
```

The built-in components render the built-in parts only. Render yours in the `datagrid_controls` block of the [list template](../crud-controller/reference/crud-controller.md#templates-and-parameters):

```twig
{% extends '@ShopsysAdministration/crud/list.html.twig' %}

{% block datagrid_controls %}
    {% set eanSearch = datagridState.get('App\\Component\\Datagrid\\EanSearch') %}
    <form method="get" class="mb-3">
        <input type="search" name="{{ eanSearch.formName }}[ean]" value="{{ eanSearch.ean }}" class="form-control" placeholder="EAN">
    </form>
    {{ parent() }}
{% endblock %}
```

- Only one search applies, see [What the administrator sees](./filters.md#what-the-administrator-sees) for the filter and the quick search. A search of your own follows those ranked by `DatagridStateFactory::getSearchPrecedence()`, in the order of the listeners, so the EAN search applies when neither the filter nor the quick search is active. To rank it, extend `DatagridStateFactory` and override `getSearchPrecedence()`, or `createCondition()` to combine the parts differently.
- A part of the same class can be added only once.
- An exact match on one field needs no code of your own — a [filter](./filters.md) of the field offers the operation "is".

### Changing a built-in part

The subscribers are open. To build a built-in part differently (other searched paths, for one), extend its subscriber — `QuickSearchStateSubscriber`, `FilterStateSubscriber` or `DomainControlStateSubscriber` — and register your class in place of it:

```yaml
Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearchStateSubscriber:
    alias: App\Component\Datagrid\QuickSearchStateSubscriber
```

A subscriber adding a part of your own instead of the built-in one leaves the built-in control out, so render your part as shown above. A datagrid with a domain control fails when no `DomainControlScope` is added.

### Other extension points

- **A field displayed on behalf of a part** — `DatagridStateEvent::addField()`. The fields are added in the order of the listeners, a field declared on the datagrid takes precedence, and the `role` and `permission` options apply.
- **The look of the controls** — override the templates of the `Admin:Grid:*` components, `@ShopsysAdministration/components/grid/*.html.twig`, in `templates/bundles/ShopsysAdministrationBundle/components/grid/` of the project.
- **The filters** — see [Filters](./filters.md).
- **A new operation** — extend `ExpressionOperatorEnum` (the constant, `getValueArity()`, `findReasonOfInapplicability()`, `getLabels()`) and teach it every adapter your project uses. Express the operation in `compare()` of a builder extending `DqlExpressionBuilder` and hand every other one to `parent::compare()`. The ORM adapter creates a builder for every query, so return yours from `create()` of your implementation of `DqlExpressionBuilderFactoryInterface`. Extend `PhpExpressionBuilder` the same way when the project uses `ArrayAdapter`. An adapter you do not teach throws `UnsupportedConditionException`.

    ```yaml
    Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum:
        alias: App\Component\Datagrid\Expression\ExpressionOperatorEnum

    Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilderFactoryInterface:
        alias: App\Component\Datagrid\Expression\ProjectDqlExpressionBuilderFactory

    App\Component\Datagrid\Expression\PhpExpressionBuilder: ~

    Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder:
        alias: App\Component\Datagrid\Expression\PhpExpressionBuilder
    ```

- **A new kind of node** — implement `ConditionInterface` and compile the node in `compile()` of your builder, as `DqlExpressionBuilder` compiles `DqlCondition`. Hand every other node to `parent::compile()`.
- **A value type of an own Doctrine type** — a type extending a string, text, GUID, small float or UTC date and time type is recognized, any other is `UNKNOWN`. To recognize it, implement `PathWalkerInterface` by decorating `PathWalker` and change only the value type of the walked path:

    ```php
    namespace App\Component\Datagrid;

    use App\Component\Doctrine\EanType;
    use Override;
    use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
    use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalkerInterface;
    use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\WalkedPath;
    use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;

    final readonly class EanAwarePathWalker implements PathWalkerInterface
    {
        public function __construct(
            private PathWalker $pathWalker,
        ) {
        }

        #[Override]
        public function walk(string $entityClass, string $path): WalkedPath
        {
            $walkedPath = $this->pathWalker->walk($entityClass, $path);

            if ($walkedPath->getFieldType() !== EanType::NAME) {
                return $walkedPath;
            }

            return new WalkedPath($walkedPath->steps, $walkedPath->description->withValueType(PathValueTypeEnum::STRING));
        }
    }
    ```

    ```yaml
    App\Component\Datagrid\EanAwarePathWalker: ~

    Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalkerInterface:
        alias: App\Component\Datagrid\EanAwarePathWalker
    ```

- **A new adapter** — implement `AdapterInterface` and compile `$request->condition` by a builder extending `AbstractExpressionBuilder`. It walks the tree and validates the values, your builder implements `compare()` for an operation, `andX()`, `orX()` and `not()` for the composition.

The aliases belong to `config/services.yaml` of the project. A class of the project whose name does not end with a suffix registered there automatically (`Subscriber`, `Factory`, `Enum`, …) is registered explicitly by `~`.

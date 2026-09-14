# Configuration

## Options Configuration

You can pass `options` array as second argument to `DatagridFactory::create()` method. These options are used to configure the datagrid.

- `roleConstant` (required, string) - The role constant that determines permission-based features in the datagrid. This is passed to the underlying Grid component to enable automatic permission checking. See [Grid Permission Integration](../internal-grid/index.md#automatic-permission-controls) for details on how permissions affect grid features.
- `name` (optional, string) - The name of the datagrid
- `crudDefinition` (optional, `Shopsys\AdministrationBundle\Component\Crud\Definition`) - Crud Definition provided by Crud Controller. It's used to define some additional configuration specific for Crud Controller
- `pagination` (optional, bool) - Enable or disable pagination. Default is `true`

## Factory Parameters

What the datagrid reads from its surroundings is not an option; `DatagridFactory::create()` derives it unless it is given by the optional parameters that follow the options:

```php
$datagrid = $datagridFactory->create($adapter, $options, $requestState, $domainControlScope);
```

- `requestState` (optional, `Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState`) - What the request says about the datagrid, the query of its quick search and filter forms. It is read from the main request by `DatagridRequestState::fromRequest($request, $gridId)`, or `DatagridRequestState::outsideRequest($gridId)` is used when there is no request (a command) - then no form is built. Pass it to build a datagrid in a test.
- `domainControlScope` (optional, `Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope`) - The domains the datagrid works with and the way the administrator picks among them. For a datagrid of a Crud Controller it is resolved from `crudDefinition` automatically, so pass it only for a standalone datagrid. When it is set and has a path to the domain (`domainId` of an entity implementing `DomainSeparatedEntityInterface`, or the `domainIdPath` of its configuration), the datagrid is limited to the domains of the scope and displays the domain of every record when the domain filter offers several domains. See [List domain control](../crud-controller/reference/crud-controller.md#list-domain-control)

Everything the administrator narrows the records by — the domain control, the quick search and the filters declared by `filters()` — is resolved for the request into the `DatagridNarrowing` returned by `Datagrid::getNarrowing()`. A fixed scope of the records belongs to the query of the adapter (`configureQuery()` of a Crud Controller). See [Narrowing the records](./narrowing.md).


## Methods Configuration

You can configure the datagrid using the `Datagrid` class. The `Datagrid` class provides methods to configure columns, filters, actions, and other features. The records are narrowed through the adapter, see [Narrowing the records](./narrowing.md).
All methods are chainable, so you can call them one after another.

```php
// ...
use Shopsys\AdministrationBundle\Component\Action\RowAction;

//...

// Enable/disable pagination
$datagrid->setPagination(true);

// Reorder columns by specified order. The columns that are not specified in the array will be appended at the end.
$datagrid->reorder(['name', 'price']);

// Default identifier is `id`. You can change it to any other data attribute that will be automatically requested from Adapter.
// It's used to identify the row. 
// Identifier is fetched automatically from the adapter with other columns.
// If you want to also display the identifier column, you can use `add()` method as you would with any other column.
$datagrid->setIdentifier('myHiddenId');

// Add column to the datagrid
$datagrid->add('name', [
    'label' => t('Name'),
    'sortable' => true,
]);

// Edit column (update label)
$datagrid->update('name', [
    'label' => t('Product name'),
]);

// Remove column from the datagrid
$datagrid->remove('name');

// Access to row actions configuration. More information in the "Row actions" section
$actions = $datagrid->rowActions();

```

## Drag-and-drop ordering

If the listed entity has a position that the user should be able to change by dragging rows, enable drag-and-drop ordering with `enableDragAndDrop()`. Pass the name of the field the listing is ordered by (typically the position field). If that field isn't already in the grid, it's added automatically with hidden visibility.

```php
// Orders ascending by default
$datagrid->enableDragAndDrop('position');

// Drag-and-drop can be turned off again (e.g. in an extension)
$datagrid->disableDragAndDrop();
```

Requirements and behavior:

- the managed entity must implement `Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface` (its `setPosition()` is used to persist the new order),
- the adapter must be entity-class-aware (e.g. the default ORM adapter); array-backed datagrids are not supported,
- the listing is always ordered by the given field, so a manual `setDefaultOrder()` cannot be combined with drag-and-drop (and removing the ordering field is blocked until `disableDragAndDrop()` is called),
- ordering by any other column is disabled and pagination is turned off, so the whole list can be reordered at once,
- while the quick search or the filter narrows the list, its rows are only listed in their order and cannot be dragged — saving the order renumbers the listed rows, so reordering a part of them would mix up the positions of the rest,
- saving the new order is handled automatically; it is available only to administrators with the edit permission.

More information about configuration-specific sections can be found in the following sections:

- [Fields](./fields.md)
- [Row Actions](./row-actions.md)

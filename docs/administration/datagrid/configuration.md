# Configuration

## Options Configuration

You can pass `options` array as second argument to `DatagridFactory::create()` method. These options are used to configure the datagrid.

- `roleConstant` (required, string) - The role constant that determines permission-based features in the datagrid. This is passed to the underlying Grid component to enable automatic permission checking. See [Grid Permission Integration](../internal-grid/index.md#automatic-permission-controls) for details on how permissions affect grid features.
- `name` (optional, string) - The name of the datagrid
- `crudConfig` (optional, object) - Crud Config provided by Crud Controller. It's used to define some additional configuration specific for Crud Controller
- `pagination` (optional, bool) - Enable or disable pagination. Default is `true`


## Methods Configuration

You can configure the datagrid using the `Datagrid` class. The `Datagrid` class provides methods to configure columns, filters, actions, and other features.
All methods are chainable, so you can call them one after another.

```php
// ...
use Shopsys\AdministrationBundle\Component\Action\RowAction

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

More information about configuration-specific sections can be found in the following sections:

- [Fields](./fields.md)
- [Row Actions](./row-actions.md)
# Configuration

## Options Configuration

You can pass `options` array as second argument to `DatagridFactory::create()` method. These options are used to configure the datagrid.

- `name` (optional, string) - The name of the datagrid
- `crudConfig` (optional, object) - Crud Config provided by Crud Controller. It's used to define some additional configuration specific for Crud Controller
- `pagination` (optional, bool) - Enable or disable pagination. Default is `true`


## Methods Configuration

You can configure the datagrid using the `Datagrid` class. The `Datagrid` class provides methods to configure columns, filters, actions, and other features.
All methods are chainable, so you can call them one after another.

```php

// Enable/disable pagination
$datagrid->setPagination(true);

// Reorder columns by specified order. The columns that are not specified in the array will be appended at the end.
$datagrid->reorder(['name', 'price']);

// Define identifier column
$datagrid->addIdentifier('id');

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

// Access to action configuration
$actions = $datagrid->actions();

// Add action to the datagrid
$actions->add('print', [
    'label' => t('Print'),
    'icon' => 'print',
    'routeName' => 'route_name', // `id` parameter is automatically passed to the URL
    'confirmMessage' => t('Are you sure you want to print this item?'), // If not set, no confirmation dialog is displayed
    'additionalParameters' => [
        'invoice' => true,
    ],
]);

// Edit already defined action (update label)
$actions->update('print', [
    'label' => t('Print invoice'),
]);

// Remove action from the datagrid
$actions->remove('print');

```

More information about configuration specific sections can be found in the following sections:

- [Fields](./fields.md)
- [Actions](./actions.md)
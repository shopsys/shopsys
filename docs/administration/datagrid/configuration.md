# Configuration

## Options Configuration

You can pass `options` array as second argument to `DatagridFactory::create()` method. These options are used to configure the datagrid.

- `name` (optional, string) - The name of the datagrid
- `crudConfig` (optional, object) - Crud Config provided by Crud Controller. It's used to define some additional configuration specific for Crud Controller
- `pagination` (optional, bool) - Enable or disable pagination. Default is `true`


## Methods Configuration

You can configure the datagrid using the `Datagrid` class. The `Datagrid` class provides methods to configure columns, filters, actions, and other features.

```php

// Enable/disable pagination
$datagrid->setPagination(true);

// Reorder columns by specified order. The columns that are not specified in the array will be appended at the end.
$datagrid->reorder(['name', 'price']);

```

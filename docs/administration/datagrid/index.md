# Datagrid

The Datagrid is a component that allows you to display a list of records as a table.

This component is built on top of the [Grid](../internal-grid/index.md) component and provides easier configuration. It can also be used as a standalone component outside of the Crud Controller.

- [Configuration](./configuration.md)
  - [Fields](./fields.md)
  - [Row actions](./row-actions.md)
  - [Filters](./filters.md)
- [Narrowing the records](./narrowing.md) — the quick search, where a condition belongs and the datagrid outside the CRUD controller

## Architecture

Datagrid uses some internal interfaces and classes to work properly and to provide decoupled architecture with the possibility to customize its behavior.

### Adapter

Adapters are used to fetch data from different sources. The adapter is responsible for fetching data and transforming it into a format that can be displayed in the Datagrid.

There are currently two adapters available:

- `Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapter` - Adapter that works with arrays
- `Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapter` - Adapter that works with Doctrine entities and it's used by [Crud Controller](../crud-controller/index.md)

You can also create your own adapter by implementing the `Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface`.

Every adapter narrows its records by the condition of the `DatasourceRequest` it is given, see [Narrowing the records](./narrowing.md#the-condition-tree). An adapter may implement `EntityClassAwareAdapterInterface` (it knows its entity class) and `PathDescribingAdapterInterface` (it describes a path before any query is built).

### Datagrid class

The `Shopsys\AdministrationBundle\Component\Datagrid\Datagrid` class is the main class that is used to configure the Datagrid. It provides methods to configure columns, filters, actions, and other features.

## Permission Integration

Datagrid acts as a decorator for the internal Grid component and automatically integrates with the RBAC permission system through the required `roleConstant` parameter. The role constant is passed to the underlying Grid component, which handles all permission-based features.

For detailed information about automatic permission controls, see [Grid Permission Integration](../internal-grid/index.md#automatic-permission-controls).


## Define Datagrid

To define a datagrid that is prepared for configuration follow these steps:

1. Create your adapter via Factory:

```php
class MyController extends AbstractController
{
    /**
    * @param Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapterFactory $arrayAdapterFactory
    * @param Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory $ormAdapterFactory
    */
    public function __construct(
        private readonly ArrayAdapterFactory $arrayAdapterFactory,
        private readonly OrmAdapterFactory $ormAdapterFactory
    ) {
    }
    
    public function myAction(): Response
    {
        // ...

        /** @var Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapter $adapter */
        $adapter = $this->arrayAdapterFactory->create($arrayData);
        
        // or
        
        /** @var Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapter $adapter */
        $adapter = $this->ormAdapterFactory->create($entityClass);
        // ...
    }
}
```

2. Create Datagrid instance via `DatagridFactory`:

```php
$datagrid = $this->datagridFactory->create($adapter, [
    'roleConstant' => 'ROLE_MY', // required - enables permission-based features
]);
```

3. Configure Datagrid:

The next step is to configure Datagrid by using the `Datagrid` class. The `Datagrid` class provides methods to configure columns, filters, actions, and other features.

More information about configuration can be found in the [Configuration](./configuration.md) section.

4. Create the state of the datagrid and render it — see [A datagrid outside the CRUD controller](./narrowing.md#a-datagrid-outside-the-crud-controller).

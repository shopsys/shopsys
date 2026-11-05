# Grid

[TOC]

## Basics

Grid is a component that displays data in a customizable table view in the administration.
It helps to present data to the user, can paginate the results, allows ordering by some columns, allows setting row priority with row rearranging, and supports actions on the row level and mass actions in general.

## Features

- Pagination
- Sorting by columns
- Drag&Drop (Sortable)
- Multiple Drag&Drop (Sortable across multiple grid instances)
- Actions (e.g., deletion of an entity in a row)
- Bulk Actions
- Inline editing
- Extendable template
- Support for multiple data sources
- Automatic permission-based feature control

## Philosophy

Each grid should be created with its own factory configuring a whole grid.
This factory can use `GridFactory` class from `shopsys/framework` package to create a basic grid object, which will be adjusted.

Grid uses an object implementing `DataSourceInterface` to obtain data to be rendered.
Read more about various data sources in the [Grid data sources](grid-data-sources.md) article.

## Permission Integration

Grids automatically integrate with the RBAC system to control access to various features based on user permissions. This ensures that users only see and can interact with functionality appropriate to their assigned roles.

### Role-Based Grid Creation

When creating a grid, you pass a role constant that determines the permission context:

```php
#[Route(path: '/administrator/list/')]
#[CanView('ROLE_ADMINISTRATOR')]
public function listAction(): Response
{
    $dataSource = new QueryBuilderDataSource($queryBuilder, 'a.id');
    
    // Pass role constant for permission checks
    $grid = $this->gridFactory->create('administratorList', $dataSource, 'ROLE_ADMINISTRATOR');
    
    return $this->render('list.html.twig', [
        'gridView' => $grid->createView(),
    ]);
}
```

### Automatic Permission Controls

The grid system automatically controls access to features based on user permissions:

#### Action Columns
Action columns are automatically filtered based on route permissions. Users only see actions they can actually perform:

```php
// These actions are only visible if user has access to the routes
$grid->addEditActionColumn('admin_administrator_edit', ['id' => 'a.id']);
$grid->addDeleteActionColumn('admin_administrator_delete', ['id' => 'a.id']);
```

#### Feature-Level Permissions

Grid features automatically respect permission levels:

- **Inline Editing**: Only enabled if user has EDIT permission for the role
- **Row Selection**: Only enabled if user has EDIT permission for the role  
- **Drag & Drop Ordering**: Only enabled if user has EDIT permission for the role
- **Add New Row**: Only shown if user has CREATE permission for the role

### Permission-Aware Inline Editing

When using inline editing, permissions are checked for each operation:

```php
class AdministratorInlineEdit extends AbstractGridInlineEdit
{
    protected function getRoleConstant(): string
    {
        return 'ROLE_ADMINISTRATOR';
    }
    
    // Automatically checks CREATE permission before allowing new rows
    // Automatically checks EDIT permission before allowing edits
}
```

### Best Practices

1. **Always specify role constants** when creating grids to enable permission checks
2. **Use route-based actions** so the system can automatically filter based on route permissions  
3. **Let the system handle permissions** rather than manually checking in templates
4. **Test with different user roles** to ensure proper permission enforcement

## Configurations

- **Display only**
    - When you just need to display some data to the user.
    - e.g., `Marketing > XML Feeds`
- **Display with action**
    - When you need to perform some actions on the grid entries, such as deletion.
    - e.g., `Marketing > Email newsletter`
- **Inline editable**
    - When the entities in the grid are simple enough and do not need a separate page for editing, you can edit them directly in the grid via AJAX.
    - e.g., `Pricing > Promo codes`
- **Drag&Drop**
    - When you need to set some ordering of your entities manually.
    - e.g., `Marketing > Slider pages`
- **Multiple Drag&Drop**
    - When you need to set some ordering of your entities manually among multiple sections.
    - e.g., `Marketing > Articles overview`
- **Selectable**
    - when you need to select entries from the grid and apply some bulk actions on them.
    - e.g., `Products > Products overview`

## Customization of grid rendering

It is really easy to customize the appearance of your grid by overriding suitable Twig blocks of the default Grid template.
Read the [separate article](grid-rendering-customization.md) for more information.

## Extending an existing grid

Usually, the grids are created in their factories (e.g., `\Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory`)
in `create()` method. If you need to add a new column, in most cases, it should be sufficient to override the method:

```php
namespace App\Grid\Payment;

use Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory as BasePaymentGridFactory;

class PaymentGridFactory extends BasePaymentGridFactory
{
    public function create()
    {
        $grid = parent::create();
        $grid->addColumn('myNewAttribute', 'p.myNewAttribute', t('My new attribute label'));

        return $grid;
    }
}
```

and then set your class as an alias for the original one in `services.yaml` configuration file:

```yaml
Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory: '@App\Grid\PaymentGridFactory'
```

However, not all grids have their own factories. Sometimes, they are created in a protected controller method, and sometimes, you may need to perform more advanced customizations (e.g., change the data source for the grid).
In such a case, you must fork the corresponding method and rewrite it to suit your needs.
We are planning some refactorings to enable easier and unified grid customizations.

### Reordering columns

If you need to reorder columns in the existing grid, you may use the `reorderColumns()` method.

```php
namespace App\Grid\Payment;

use Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory as BasePaymentGridFactory;

class PaymentGridFactory extends BasePaymentGridFactory
{
    public function create()
    {
        $grid = parent::create();

        $grid->reorderColumns([
            'enabled',
            'name',
        ]);

        return $grid;
    }
}
```

Grid columns will be rendered in the order they are defined in the `reorderColumns()` method.
If any column is missing in the `reorderColumns` method call, it will be rendered after the mentioned columns in the original order.

## Further reading - cookbooks

If you want to implement a new grid, you can follow the cookbooks:

- [Create basic grid](../../cookbook/create-basic-grid.md)
- [Create advanced grid](../../cookbook/create-advanced-grid.md)

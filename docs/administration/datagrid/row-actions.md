# Row actions

Row actions are buttons that are displayed in the last column of the grid. They are used for performing actions on a single row.

!!! info "Automatic Permission Filtering"

    Row actions that link to routes are automatically filtered based on user permissions. The underlying Grid component checks if the user has access to the target route before displaying the action. This ensures users only see actions they can actually perform, based on the `roleConstant` provided to the datagrid.

## Configuration

Row actions are defined by the `Datagrid` class using the `actions()` method. The method returns an instance of the `DatagridRowActions` class that is used to configure row actions.

```php
// ...
use Shopsys\AdministrationBundle\Component\Action\RowAction;

//...

/** @var \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid */
$datagrid = $datagridFactory->create($adapter, [
    'roleConstant' => 'ROLE_ARTICLE', // Required for permission checks
]);

/** @var \Shopsys\AdministrationBundle\Component\Datagrid\DatagridRowActions $rowActions */
$rowActions = $datagrid->actions();

// Add action to the datagrid
$rowActions->add(
    RowAction::create('print', t('Print'), 'print')
        ->linkToRoute('route_name', fn($row) => ['id' => $row['id'], 'invoice' => true])
        ->setConfirmMessage(t('Are you sure you want to print this item?'))
);

// Edit already defined action (update label)
$rowActions->update('print', fn(RowAction $action) => $action->setLabel(t('Print invoice')));

// Remove action from the datagrid
$rowActions->remove('print');

// Finally, you can also reorder actions by specified order. The actions that are not specified in the array will be appended at the end.
$rowActions->reorder(['edit', 'delete']);
```

## RowAction

The `RowAction` class is used to define a single row action. It provides methods to configure the action.
Creating a new instance of the `RowAction` class is done using the `create()` method with the following parameters:
- `$name` (string) - The name of the action. It is used to identify the action.
- `$label` (string) - The label of the action.
- `$icon` (string) - The icon of the action. 

```php
// ...
use App\Controller\Admin\ArticleController;
use App\Model\Article\Article;
use Shopsys\AdministrationBundle\Component\Action\RowAction
use Shopsys\AdministrationBundle\Component\Config\ActionType;

//...

$rowAction = RowAction::create('name', t('Label'), 'icon')
    // This will change label
    ->setLabel(t('New publish'))
    // You can also set an icon if you want
    ->setIcon('eye')
    // If the icon is not enough, you can also set additional CSS class
    ->setAdditionalClass('btn-primary')
    // or any other attribute (Some attributes are used internally and cannot be overridden)
    ->setAttribute('data-attribute', 'value')
    // You can conditionally display action only when you need it
    ->displayIf(fn (array $row) => $row['domainId'] === 1)
    // By default, the tooltip is displayed after hovering over the action. You can disable it if you want
    ->showTooltip(false)
    // You can also disable the action based on some condition. The action will be displayed but not clickable
    // Message will be shown on hover
    ->setDisabledMessage(fn (array $row) => $row['isPublished'] ? t('Article is already published') : null)
    // You can also use more complex logic
    ->setDisabledMessage(function ($row) {
        if ($row['isPublished']) { return t('Article is already published'); }
        if ($row['title'] === '') { return t('Article must have a title to be published'); }
        
        return null;
    })
    ;

$rowAction
    // Creates a link to a specific route. As the second argument you should specify parameters with an array or Closure
    ->linkToRoute('admin_article_publish', fn (array $row) => [ 'id' => $row['id'] ])
    // Use this in case you need to create a link to an external page
    ->linkToUrl(fn (array $row) => 'https://www.google.com/search?q=' . $row['title'])
    // Creates a link between CRUD controllers
    ->linkToCrud(ArticleController::class, ActionType::DETAIL)     
    // Use in case your page is too important to lose it  
    ->setOpenInNewTab()
    // In case, your action will do some changes in the database, you can set a confirm message to prevent some mistakes
    ->setConfirmMessage(t('Are you sure you want to publish this article?'))
    ;
```

!!! note
    You can implement your own reusable actions by extending the `Shopsys\AdministrationBundle\Component\Action\AbstractAction` class and implementing `Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface`.

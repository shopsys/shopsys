## Configure List Page

We have created a new Crud Controller and now want to configure the list page. The list page displays a list of records from the database in a datagrid component. 
Crud Controller provides some methods that allow you to configure the Datagrid component.

## 1. Define Datagrid columns

The first step is to define a datagrid to display the list of records. The list of methods for configuring a datagrid can be found in the [Datagrid Methods Configuration](../../datagrid/configuration.md#methods-configuration) reference.

To start, we can define a simple datagrid with three columns: `id`, `number`, and `createdAt`.

```php

public function configureDatagrid(Datagrid $datagrid): void
{
    $datagrid
        ->add('id', [
            'label' => t('Id'),
        ])
        ->add('number', [
            'label' => t('Order Nr.'),
        ])
        ->add('createdAt', [
            'label' => t('Created at'),
        ])
    ;
}
```
!!! note

    Every column can be configured by passing an array with options. The list of available options can be found in the [Fields Configuration](../../datagrid/fields.md) reference.

## 2. Modify Datagrid query

In some cases, you may want to modify the query that is used to fetch the records. You can do that by using the `configureQuery` method. The method receives a query builder as an argument, so you can use all the methods provided by the Doctrine Query Builder.

```php

/**
 * @param \Doctrine\ORM\QueryBuilder $queryBuilder
 */
protected function configureQuery(QueryBuilder $queryBuilder): void
{
    $queryBuilder
        ->addSelect('CASE WHEN o.companyName IS NOT NULL
                THEN o.companyName
                ELSE CONCAT(o.lastName, \' \', o.firstName)
            END AS customerName')
        ->andWhere('o.deleted = :deleted')
        ->setParameter('deleted', false);
}

/**
 * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
 */
public function configureDatagrid(Datagrid $datagrid): void
{

    // ...
    $datagrid
        // Option `virtual` means that this column name will not be used in the query. Property is used to get the value from the query.
        ->add('customerName', [
            'label' => t('Customer Name'),
            'virtual' => true,
            'property' => 'customerName',
        ])
    ;
    
    // To see orders sorted by the creation date, you can set the default order
    $datagrid->setDefaultOrder('createdAt', OrderingEnum::DESC);
}
```

## 3. Configure list actions

Sometimes you may want to add some actions on the list page. You can do that by using the `configureActions` method. This method is also used for configuring actions on other pages, so you can use the `ActionsConfig` object to define the actions that will be displayed on the list page.

```php

// ...
use Shopsys\AdministrationBundle\Component\Action;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;

// ...

/**
 * @param \Shopsys\AdministrationBundle\Component\Config\ActionsConfig $actions
 */
protected function configureActions(ActionsConfig $actions): void
{

    // This will add an action with the name `linkToDashboard` on the `ActionType::LIST` page
    $actions->add(
        ActionType::LIST,
        Action::create('linkToDashboard', t('Link To dashboard'))
            ->setAttribute('class', 'btn-primary', true)
            ->setOpenInNewTab()
            ->linkToRoute('admin_default_dashboard', fn () => [
                'id' => 1,
            ]),
    );
}
```

!!! note

    You can look under the hood of the Actions in the [Actions reference](../reference/actions.md) section. 

## 4. Domain filter (automatic)

If the entity is domain-aware, the list page automatically renders a domain switcher above the grid and scopes the grid to the selected domain — no configuration needed. The behavior is detected from the entity's Doctrine mapping (scalar `domainId` field, or a `domains` `OneToMany` association). You can override or disable it in `configure()`. See [Domain filter](../reference/domain-filter.md) for details.

And that's it. Now you have a list page configured and ready to be used. You can continue with [Extending existing Crud Controller](extending-existing-crud-controller.md) guide to see how to extend the Crud Controller with custom functionality.

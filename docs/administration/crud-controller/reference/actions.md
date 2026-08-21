# Actions

Actions provide a simple way for administrators to navigate the administration. You can use them for basic navigation between crud controllers, pages, or your custom controllers and actions.

Actions can be configured in `configureActions()` method of the Crud Controller.

```php
namespace App\Controller\Admin;

use App\Model\Article\Article;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;

#[CrudController(Article::class)]
class ArticleController extends AbstractCrudController
{
    // ...

    protected function configureActions(ActionsConfig $actions): void
    {
        
    }
}
```

## Default actions

The Crud Controller provides a set of default actions that are displayed based on the Crud Controller configuration and specific page.

!!! note

    Default actions are displayed only if corresponding conditions are met (e.g. action is enabled).


### New action

- **Action Name**   : `create`
- **Action**             : Redirects to the `ActionType::CREATE` of the current Crud Controller.
- **Displayed on**       : `ActionType::LIST` page


## Custom actions

You can add, update or remove actions in the `configureActions()` method.

```php
// ...

use App\Model\Article\Article;
use Shopsys\AdministrationBundle\Component\Action\AbstractAction;
use Shopsys\AdministrationBundle\Component\Action\Action;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;

// ...

/**
 * @param \Shopsys\AdministrationBundle\Component\Config\ActionsConfig $actions
 */
protected function configureActions(ActionsConfig $actions): void
{
    // This will add an action with the name `Publish` on the `ActionType::EDIT` page
    $actions->add(
        ActionType::EDIT,
        Action::create('publish', t('Publish'))
            ->linkToRoute('admin_article_publish', fn (Article $entity) => [
                'id' => $entity->getId(),
            ]),
    );
    
    // You can also update existing actions
    $actions->update(
        ActionType::LIST,
        'publish',
        fn (AbstractAction $action) => $action->setIcon('eye'),
    );
    
    // Or remove them
    $actions->remove(ActionType::EDIT, 'publish');
}
```

!!! note

    You can implement your own reusable actions by extending the `Shopsys\AdministrationBundle\Component\Action\AbstractAction` class.

### What the closures receive

The `displayIf()` and `linkToRoute()` closures receive the data of the page the action is displayed on:

- `ActionType::EDIT` — the edited entity
- `ActionType::LIST`, `ActionType::CREATE`, and `ActionType::DETAIL` — `null` (there is no single entity on these pages; for per-row actions in the list grid use row actions instead — their closures receive the data of the grid row they are rendered in)

### Configuration

```php
// ...

use App\Controller\Admin\ArticleController;
use App\Model\Article\Article;
use Shopsys\AdministrationBundle\Component\Action\Action;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;

// ...

protected function configureActions(ActionsConfig $actions): void
{
    // create new action requires internal name and label
    $newAction = Action::create('publish', t('Publish'))
        // This will change label
        ->setLabel(t('New publish'))
        // You can also set an icon if you want
        ->setIcon('eye')
        // If the icon is not enough, you can also set your custom CSS class
        ->setAttribute('class', 'btn-primary', true)
        // or any other attribute
        ->setAttribute('data-attribute', 'value')
        // You can conditionally display action only when you need it
        ->displayIf(fn (Article $entity) => $article->isPublished() === false)
    ;
    
    // You will probably want to redirect the user after clicking to Action. Some methods will help you with that
    // However, if you do not use any of the following features, the action will still be displayed
    $newAction
        // Creates a link to a specific route. As the second argument you can specify parameters with an array or Closure
        ->linkToRoute('admin_default_dashboard')
        // Use this in case you need to create an action to an external page.
        ->linkToUrl('https://www.shopsys.com')
        // Creates a link between CRUD controllers
        ->linkToCrud(ArticleController::class, ActionType::List)     
        // Use in case your page is too important to lose it  
        ->setOpenInNewTab()
    ;
    
    $actions->add(ActionType::EDIT, $newAction);
}
```

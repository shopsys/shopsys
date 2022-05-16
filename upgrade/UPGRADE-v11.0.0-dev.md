# [Upgrade from v10.0.0 to v11.0.0-dev](https://github.com/shopsys/shopsys/compare/v10.0.0...master)

This guide contains instructions to upgrade from version v10.0.0 to v11.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## Removed deprecations

- check that your code don't use any removed code ([#](https://github.com/shopsys/shopsys/pull/))
    - Phing property `path.env.test` was removed
    - function `getCategoriesOrderingData()` from `@shopsys/framework/assets/js/admin/components/CategoryTreeSorting.js` was removed, use `getNestedSetData()` instead
    - method `Shopsys\FrameworkBundle\Controller\Admin\CategoryController::saveOrderAction()` was removed, use `applySortingAction()` instead
    - method `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::editOrdering()` was removed, use `reorderByNestedSetValues()` instead
    - data transformer `Shopsys\FrameworkBundle\Form\Transformers\ScriptPlacementToBooleanTransformer` was removed as it's not necessary anymore
